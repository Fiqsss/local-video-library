<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use App\Models\ModelName;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;

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
        return view('videos.index', compact('videos', 'categories', 'models', 'pageCategory'));
    }

    public function show(Video $video)
    {
        $video->load(['category', 'categories', 'models']);
        $recommendations = Video::with(['category', 'categories', 'models'])->whereKeyNot($video->id)->latest()->take(8)->get();
        return view('videos.show', compact('video', 'recommendations'));
    }

    public function stream(Video $video)
    {
        abort_unless($video->source_type === 'local', 404);

        $path = $this->localVideoPath($video->source_path);
        abort_unless($path && is_file($path) && is_readable($path), 404, 'File video tidak ditemukan.');

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
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
        return view('admin.index', compact('videos', 'categories', 'models', 'totalVideos'));
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
        return redirect()->route('admin.videos.index')->with('success', 'berhasil diperbarui.');
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
        $data['model_name'] = !empty($data['model_ids']) ? ModelName::whereKey((int) $data['model_ids'][0])->value('name') : null;
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
            'model_name' => $modelIds[0] ? ModelName::whereKey($modelIds[0])->value('name') : null,
        ]);
    }
}
