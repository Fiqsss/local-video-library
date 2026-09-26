<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use App\Models\ModelName;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = Video::with(['category', 'categories', 'models'])->latest();
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('model_name', 'like', "%{$term}%")
                ->orWhereHas('models', fn($m) => $m->where('name', 'like', "%{$term}%"))
                ->orWhereHas('categories', fn($c) => $c->where('name', 'like', "%{$term}%")));
        }
        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->string('category')));
        }
        if ($request->filled('model')) {
            $query->whereHas('models', fn($q) => $q->where('id', $request->integer('model')));
        }
        $videos = $query->paginate(20)->withQueryString();
        $categories = Category::withCount('videos')->orderBy('name')->get();
        $models = ModelName::withCount('videos')->orderBy('name')->get();
        $pageCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->string('category')->toString())
            : null;
        $homeVideoData = $videos->getCollection()->map(fn ($video) => [
            'id' => $video->id,
            'title' => $video->title,
            'duration' => $video->duration,
            'thumbnail_url' => $video->thumbnail_url,
            'thumbnail_src' => $video->thumbnail_url ?: (Storage::disk('public')->exists('thumbnails/' . $video->id . '.jpg') ? route('videos.thumbnail', $video) : null),
            'playback_url' => $video->playback_url,
            'categories' => $video->categories->pluck('name')->values(),
            'models' => $video->models->map(fn ($model) => ['id' => $model->id, 'name' => $model->name])->values(),
            'url' => route('videos.show', $video),
        ])->values();
        $homePagination = [
            'current' => $videos->currentPage(),
            'last' => $videos->lastPage(),
            'prev' => $videos->previousPageUrl(),
            'next' => $videos->nextPageUrl(),
        ];
        return view('videos.index', compact('videos', 'categories', 'models', 'pageCategory', 'homeVideoData', 'homePagination'));
    }

    public function show(Video $video)
    {
        $video->load(['category', 'categories', 'models']);
        $recommendations = $this->recommendationQuery($video)->paginate(9, $this->relatedColumns(), 'related_page');
        $recentVideos = Video::query()
            ->select($this->relatedColumns())
            ->with('category:id,name,slug')
            ->whereKeyNot($video->id)
            ->latest()
            ->limit(4)
            ->get();
        $totalVideos = Video::count();
        $todayVideos = Video::whereDate('created_at', now()->toDateString())->count();

        return view('videos.show', compact('video', 'recommendations', 'recentVideos', 'totalVideos', 'todayVideos'));
    }

    public function related(Request $request, Video $video)
    {
        $recommendations = $this->recommendationQuery($video)->paginate(9, $this->relatedColumns(), 'page');

        $data = $recommendations->getCollection()->map(fn (Video $item) => [
            'id' => $item->id,
            'title' => $item->title,
            'duration' => $item->duration,
            'thumbnail_url' => $item->thumbnail_url,
            'thumbnail_src' => $item->thumbnail_url ?: ($item->source_type === 'local' ? route('videos.thumbnail', $item) : null),
            'category' => $item->category?->name,
            'model' => $item->model_name,
            'url' => route('videos.show', $item),
        ])->values();

        return response()->json([
            'data' => $data,
            'current_page' => $recommendations->currentPage(),
            'last_page' => $recommendations->lastPage(),
            'has_more' => $recommendations->hasMorePages(),
        ]);
    }

    private function recommendationQuery(Video $video)
    {
        $modelIds = $video->relationLoaded('models')
            ? $video->models->pluck('id')
            : $video->models()->pluck('models.id');
        $query = Video::query()
            ->select($this->relatedColumns())
            ->with('category:id,name,slug')
            ->whereKeyNot($video->id);

        if ($modelIds->isNotEmpty()) {
            $query->withCount([
                'models as matching_model_count' => fn ($relation) => $relation->whereIn('models.id', $modelIds),
            ])->orderByDesc('matching_model_count');
        }

        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    private function relatedColumns(): array
    {
        return ['id', 'title', 'duration', 'thumbnail_url', 'source_type', 'category_id', 'model_name', 'created_at'];
    }

    public function stream(Video $video)
    {
        abort_unless($video->source_type === 'local', 404);

        $path = $this->localVideoPath($video->source_path);
        abort_unless($path && is_file($path) && is_readable($path), 404, 'File video tidak ditemukan.');

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function previewFile(Request $request)
    {
        $path = $this->localVideoPath($request->query('path', ''));
        abort_unless($path && is_file($path) && is_readable($path), 404, 'File video tidak ditemukan.');

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    public function thumbnail(Request $request, Video $video)
    {
        abort_unless($video->source_type === 'local', 404);

        if ($video->thumbnail_url && filter_var($video->thumbnail_url, FILTER_VALIDATE_URL)) {
            return redirect()->away($video->thumbnail_url);
        }

        $relativePath = 'thumbnails/' . $video->id . '.jpg';
        if (Storage::disk('public')->exists($relativePath)) {
            return response()->file(Storage::disk('public')->path($relativePath), [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        // Normal homepage requests must never block on FFmpeg. Generation is explicit.
        if (!$request->boolean('generate')) {
            return response()->json(['message' => 'Thumbnail belum tersedia.'], 404);
        }

        $source = $this->localVideoPath($video->source_path);
        abort_unless($source && is_file($source) && is_readable($source), 404);

        Storage::disk('public')->makeDirectory('thumbnails');
        $output = Storage::disk('public')->path($relativePath);
        $ffmpeg = $this->resolveExecutable('ffmpeg_path', 'ffmpeg.exe');
        if (!$ffmpeg) return response()->json(['message' => 'FFmpeg tidak ditemukan. Atur FFMPEG_PATH di file .env.'], 503);
        [$exitCode, , $error] = $this->runExternalCommand([$ffmpeg, '-y', '-ss', '00:00:01', '-i', $source, '-frames:v', '1', '-vf', 'scale=' . config('video.thumbnail_width', 640) . ':-2', '-q:v', '5', $output], config('video.process_timeout', 30));
        if ($exitCode !== 0) Log::error('FFmpeg thumbnail gagal.', ['video_id' => $video->id, 'error' => $error]);

        abort_unless($exitCode === 0 && is_file($output), 503, 'Thumbnail belum dapat dibuat. Pastikan FFmpeg terpasang.');

        return response()->file($output, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    public function metadata(Video $video)
    {
        if ($video->duration) {
            return response()->json(['duration' => $video->duration]);
        }

        $source = $this->localVideoPath($video->source_path);
        abort_unless($video->source_type === 'local' && $source && is_file($source), 404);
        $ffprobe = $this->resolveExecutable('ffprobe_path', 'ffprobe.exe');
        if (!$ffprobe) return response()->json(['message' => 'FFprobe tidak ditemukan. Atur FFPROBE_PATH di file .env.'], 503);
        [$exitCode, $output, $error] = $this->runExternalCommand([$ffprobe, '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', $source], 15);
        $seconds = (float) trim($output);
        if ($exitCode !== 0) Log::error('FFprobe duration gagal.', ['video_id' => $video->id, 'error' => $error]);
        abort_unless($exitCode === 0 && $seconds > 0, 503);

        $duration = $this->formatDuration($seconds);
        $video->update(['duration' => $duration]);

        return response()->json(['duration' => $duration], 200, ['Cache-Control' => 'public, max-age=31536000']);
    }

    private function localVideoPath(string $sourcePath): ?string
    {
        $path = trim($sourcePath);
        if (str_starts_with(strtolower($path), 'file://')) {
            $path = rawurldecode(parse_url($path, PHP_URL_PATH) ?? '');
            if (preg_match('~^/[a-z]:~i', $path)) $path = ltrim($path, '/');
        }
        $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $resolved = realpath($normalizedPath);
        if ($resolved) {
            return $resolved;
        }

        if (!preg_match('~^[a-z]:[\\\\/]~i', $normalizedPath) && !str_starts_with($normalizedPath, DIRECTORY_SEPARATOR)) {
            foreach ([public_path($normalizedPath), base_path($normalizedPath)] as $candidate) {
                $resolved = realpath($candidate);
                if ($resolved) {
                    return $resolved;
                }
            }
        }

        return null;
    }

    public function browseFiles(Request $request)
    {
        $rootConfig = env('VIDEO_BROWSE_ROOT');
        $path = $request->query('path', 'D:\\');
        $path = realpath($path);

        if ($rootConfig) {
            $root = realpath($rootConfig);
            abort_unless($root && $path, 403);
            $rootPrefix = rtrim($root, '\\/') . DIRECTORY_SEPARATOR;
            abort_unless($path === $root || str_starts_with(strtolower($path), strtolower($rootPrefix)), 403);
        } else {
            abort_unless($path, 403);
        }

        abort_unless(is_dir($path), 404);

        $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi'];
        $term = trim((string) $request->query('q', ''));
        $entries = collect(File::directories($path))->map(fn($directory) => [
            'name' => basename($directory),
            'path' => $directory,
            'type' => 'directory',
        ])->merge(collect(File::files($path))->filter(fn($file) => in_array(strtolower($file->getExtension()), $videoExtensions, true))->map(fn($file) => [
            'name' => $file->getFilename(),
            'path' => $file->getPathname(),
            'type' => 'file',
        ]))->filter(fn($entry) => $term === '' || str_contains(strtolower($entry['name']), strtolower($term)))
            ->sortBy([['type', 'desc'], ['name', 'asc']])->values();

        if ($rootConfig) {
            $parent = ($path === $root) ? null : dirname($path);
        } else {
            $isRootDrive = preg_match('~^[a-z]:\\\\?$~i', $path);
            $parent = $isRootDrive ? null : dirname($path);
        }

        return response()->json(['current' => $path, 'parent' => $parent, 'entries' => $entries]);
    }

    public function admin()
    {
        $videos = Video::with(['category', 'categories', 'models'])->latest()->paginate(10);
        $totalVideos = Video::count();
        $categories = Category::withCount('videos')->orderBy('name')->get();
        $models = ModelName::withCount('videos')->orderBy('name')->get();
        $missingVideos = Video::where('source_type', 'local')
            ->latest()
            ->get()
            ->filter(fn (Video $video) => !is_file($this->localVideoPath($video->source_path) ?? ''))
            ->values();
        $editVideo = request()->filled('edit')
            ? Video::with(['categories', 'models'])->find(request()->integer('edit'))
            : null;
        return view('admin.index', compact('videos', 'categories', 'models', 'totalVideos', 'editVideo', 'missingVideos'));
    }

    public function duplicates()
    {
        $groups = Video::query()
            ->selectRaw('LOWER(TRIM(title)) AS normalized_title, COUNT(*) AS duplicate_count')
            ->groupByRaw('LOWER(TRIM(title))')
            ->having('duplicate_count', '>', 1)
            ->orderByDesc('duplicate_count')
            ->get();
        $duplicates = $groups->map(function ($group) {
            $videos = Video::with(['categories', 'models', 'category'])
                ->whereRaw('LOWER(TRIM(title)) = ?', [$group->normalized_title])
                ->latest()
                ->get()
                ->map(function (Video $video) {
                    $path = $video->source_type === 'local' ? $this->localVideoPath($video->source_path) : null;
                    $video->file_available = $path && is_file($path) && is_readable($path);
                    $video->file_size = $video->file_available ? filesize($path) : 0;
                    $video->has_thumbnail = (bool) $video->thumbnail_url || Storage::disk('public')->exists('thumbnails/' . $video->id . '.jpg');
                    $video->metadata_score = ($video->file_available ? 1000000 : 0) + ($video->file_size > 0 ? 100000 : 0) + ($video->duration ? 10000 : 0) + ($video->has_thumbnail ? 1000 : 0) + ($video->categories->isNotEmpty() ? 100 : 0) + ($video->models->isNotEmpty() ? 10 : 0);
                    return $video;
                })
                ->sortByDesc(fn (Video $video) => [$video->metadata_score, $video->created_at->timestamp])
                ->values();
            $keep = $videos->first();
            return [
                'normalized' => $group->normalized_title,
                'title' => $keep?->title,
                'count' => (int) $group->duplicate_count,
                'keep' => $keep?->id,
                'reason' => $keep ? $this->recommendationReason($keep) : '',
                'videos' => $videos,
            ];
        });
        return view('admin.duplicates', compact('duplicates'));
    }

    public function deleteDuplicates(Request $request)
    {
        $data = $request->validate(['video_ids' => ['required', 'array', 'min:1'], 'video_ids.*' => ['integer', 'exists:videos,id']]);
        $ids = array_values(array_unique(array_map('intval', $data['video_ids'])));
        $deleted = DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $relative = 'thumbnails/' . $id . '.jpg';
                if (Storage::disk('public')->exists($relative)) Storage::disk('public')->delete($relative);
            }
            return Video::whereIn('id', $ids)->delete();
        });
        return redirect()->route('admin.videos.duplicates')->with('success', "{$deleted} video duplicate berhasil dihapus.");
    }

    public function deleteRecommendedDuplicate(Request $request)
    {
        $data = $request->validate(['keep_id' => ['required', 'integer', 'exists:videos,id'], 'delete_ids' => ['required', 'array', 'min:1'], 'delete_ids.*' => ['integer', 'exists:videos,id']]);
        $keep = Video::findOrFail($data['keep_id']);
        $ids = array_values(array_unique(array_map('intval', $data['delete_ids'])));
        $sameTitle = Video::whereIn('id', array_merge([$keep->id], $ids))->selectRaw('COUNT(DISTINCT LOWER(TRIM(title))) AS groups')->value('groups');
        abort_unless((int) $sameTitle === 1 && !in_array($keep->id, $ids, true), 422, 'Group duplicate tidak valid.');
        $request->merge(['video_ids' => $ids]);
        return $this->deleteDuplicates($request);
    }

    public function deleteAllRecommendedDuplicates(Request $request)
    {
        $data = $request->validate(['keep_ids' => ['required', 'array', 'min:1'], 'keep_ids.*' => ['integer', 'exists:videos,id'], 'delete_ids' => ['nullable', 'array'], 'delete_ids.*' => ['integer', 'exists:videos,id']]);
        $keepIds = array_values(array_unique(array_map('intval', $data['keep_ids'])));
        $deleteIds = array_values(array_unique(array_map('intval', $data['delete_ids'] ?? [])));
        if (array_intersect($keepIds, $deleteIds)) abort(422, 'Video rekomendasi tidak boleh ikut dihapus.');
        foreach ($deleteIds as $deleteId) {
            $title = Video::whereKey($deleteId)->value('title');
            $hasKeep = Video::whereIn('id', $keepIds)->whereRaw('LOWER(TRIM(title)) = LOWER(TRIM(?))', [$title])->exists();
            if (!$hasKeep) abort(422, 'Setiap group duplicate harus memiliki video yang dipertahankan.');
        }
        $request->merge(['video_ids' => $deleteIds]);
        return $this->deleteDuplicates($request);
    }

public function store(Request $request)
    {
        $data = $this->validated($request);
        $paths = $data['selected_files'] ?? [$data['external_url']];
        $createdCount = count($paths);
        $titles = $data['file_titles'] ?? [];
        $modelIds = $data['model_ids'] ?? [];
        foreach ($paths as $index => $path) {
            $videoData = $data;
            $videoData['source_path'] = $path;
            $videoData['title'] = $titles[$index] ?? ($data['title'] ?: pathinfo($path, PATHINFO_FILENAME));
            unset($videoData['external_url'], $videoData['selected_files'], $videoData['file_titles']);
            $video = Video::create($videoData);
            $this->syncTags($video, $request, $modelIds);
        }
        return redirect()->route('admin.videos.index')->with('success', $createdCount === 1
            ? 'berhasil ditambahkan.'
            : "{$createdCount} videoimmel Successfully ditambahkan.");
    }

public function update(Request $request, Video $video)
    {
        $data = $this->validated($request, $video);
        $data['source_path'] = $data['external_url'];
        unset($data['external_url']);
        $video->update($data);
        $this->syncTags($video, $request, $data['model_ids'] ?? []);
        return redirect()->route('videos.show', $video)->with('success', 'Video berhasil diperbarui.');
    }

    public function destroy(Video $video)
    {
        $video->delete();
        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'video_ids' => ['required', 'array', 'min:1'],
            'video_ids.*' => ['integer', 'exists:videos,id'],
        ]);
        $deleted = Video::whereIn('id', $data['video_ids'])->delete();

        return redirect()->route('admin.videos.index')->with('success', "{$deleted} video berhasil dihapus.");
    }

    private function validated(Request $request, ?Video $video = null): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:180', 'required_without:selected_files'],
            'duration' => ['nullable', 'regex:/^\d{1,3}:[0-5]\d$/'],
            'model_ids' => ['nullable', 'array'],
            'model_ids.*' => ['integer', 'exists:models,id'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'source_type' => ['required', Rule::in(['local', 'url'])],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'external_url' => ['nullable', 'string', 'max:2048', 'required_without:selected_files'],
            'selected_files' => ['nullable', 'array', 'min:1'],
            'selected_files.*' => ['required', 'string', 'max:2048'],
            'file_titles' => ['nullable', 'array', 'required_with:selected_files'],
            'file_titles.*' => ['required', 'string', 'max:180'],
        ];
        $data = $request->validate($rules);
        if ($request->input('source_type') === 'local' && $request->filled('selected_files')) {
            $data['external_url'] = $request->input('selected_files.0');
        }
        if ($data['source_type'] === 'local') {
            $paths = $data['selected_files'] ?? [$data['external_url']];
            $invalid = collect($paths)->first(fn($path) => !is_file($this->localVideoPath($path) ?? ''));
            if ($invalid) {
                throw ValidationException::withMessages([
                    'external_url' => 'File video tidak ditemukan atau tidak bisa dibaca: ' . $invalid,
                ]);
            }
        }
        $data['category_id'] = (int) $data['category_ids'][0];
        $data['model_name'] = !empty($data['model_ids']) ? ModelName::whereKey((int) $data['model_ids'][0])->value('name') : '';
        if ($data['source_type'] === 'url') $data['source_path'] = $data['external_url'];
        return $data;
    }

    private function syncTags(Video $video, Request $request, ?array $modelIds = null): void
    {
        $categoryIds = array_values(array_unique(array_map('intval', $request->input('category_ids', []))));
        $modelIds = $modelIds ?? array_values(array_unique(array_map('intval', $request->input('model_ids', []))));
        $video->categories()->sync($categoryIds);
        $video->models()->sync($modelIds);
        $video->update([
            'category_id' => $categoryIds[0] ?? null,
            'model_name' => $modelIds[0] ? ModelName::whereKey($modelIds[0])->value('name') : '',
        ]);
    }

    private function formatDuration(float $seconds): string
    {
        $total = (int) round($seconds);
        $hours = intdiv($total, 3600);
        $minutes = intdiv($total % 3600, 60);
        $remaining = $total % 60;
        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $remaining)
            : sprintf('%02d:%02d', $minutes, $remaining);
    }

    private function recommendationReason(Video $video): string
    {
        $parts = [];
        if ($video->file_available) $parts[] = 'file tersedia';
        if ($video->file_size > 0) $parts[] = 'ukuran file valid';
        if ($video->duration) $parts[] = 'duration tersedia';
        if ($video->has_thumbnail) $parts[] = 'thumbnail tersedia';
        if ($video->categories->isNotEmpty() || $video->models->isNotEmpty()) $parts[] = 'metadata lengkap';
        return ucfirst(implode(', ', $parts)) . '.';
    }

    private function resolveExecutable(string $configKey, string $fallback): ?string
    {
        $candidate = trim((string) (config('video.' . $configKey) ?: $fallback), " \t\n\r\0\x0B\"");
        if (preg_match('~^[a-z]:[\\/]~i', $candidate) || str_contains($candidate, '\\')) {
            if (is_file($candidate) && is_executable($candidate)) return realpath($candidate) ?: $candidate;
            Log::warning('Executable media tidak ditemukan.', ['config' => $configKey, 'path' => $candidate]);
            return null;
        }
        [$exitCode, $output] = $this->runExternalCommand(['where.exe', $candidate], 5);
        if ($exitCode === 0 && trim($output) !== '') return trim(strtok($output, "\r\n"));
        Log::warning('Executable media tidak ada di PATH.', ['config' => $configKey, 'command' => $candidate]);
        return null;
    }

    private function runExternalCommand(array $arguments, int $timeout): array
    {
        $process = new Process(array_map(static fn ($argument) => (string) $argument, $arguments));
        $process->setTimeout($timeout);
        try {
            $process->run();
        } catch (\Throwable $exception) {
            return [1, '', $exception->getMessage()];
        }
        return [$process->getExitCode() ?? 1, trim($process->getOutput()), trim($process->getErrorOutput())];
    }

}
