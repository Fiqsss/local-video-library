<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = Video::with('category')->latest();
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('model_name', 'like', "%{$term}%")
                ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%")));
        }
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->string('category')));
        }
        $videos = $query->paginate(24)->withQueryString();
        $categories = Category::withCount('videos')->orderBy('name')->get();
        return view('videos.index', compact('videos', 'categories'));
    }

    public function show(Video $video)
    {
        $video->load('category');
        $recommendations = Video::with('category')->whereKeyNot($video->id)->latest()->take(8)->get();
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
        return realpath(str_replace('/', DIRECTORY_SEPARATOR, $path)) ?: null;
    }

    public function browseFiles(Request $request)
    {
        $root = realpath(env('VIDEO_BROWSE_ROOT', 'D:\\'));
        $path = $request->query('path', $root);
        $path = realpath($path);

        $rootPrefix = rtrim($root, '\\/') . DIRECTORY_SEPARATOR;
        abort_unless($root && $path && ($path === $root || str_starts_with(strtolower($path), strtolower($rootPrefix))), 403);
        abort_unless(is_dir($path), 404);

        $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi'];
        $entries = collect(File::directories($path))->map(fn($directory) => [
            'name' => basename($directory),
            'path' => $directory,
            'type' => 'directory',
        ])->merge(collect(File::files($path))->filter(fn($file) => in_array(strtolower($file->getExtension()), $videoExtensions, true))->map(fn($file) => [
            'name' => $file->getFilename(),
            'path' => $file->getPathname(),
            'type' => 'file',
        ]))->sortBy([['type', 'desc'], ['name', 'asc']])->values();

        return response()->json(['current' => $path, 'parent' => $path === $root ? null : dirname($path), 'entries' => $entries]);
    }

    public function admin()
    {
        $videos = Video::with('category')->latest()->get();
        $categories = Category::withCount('videos')->orderBy('name')->get();
        return view('admin.index', compact('videos', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['source_path'] = $data['external_url'];
        unset($data['external_url']);
        Video::create($data);
        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil ditambahkan.');
    }

    public function update(Request $request, Video $video)
    {
        $data = $this->validated($request, $video);
        $data['source_path'] = $data['external_url'];
        unset($data['external_url']);
        $video->update($data);
        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil diperbarui.');
    }

    public function destroy(Video $video)
    {
        $video->delete();
        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil dihapus.');
    }

    private function validated(Request $request, ?Video $video = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'duration' => ['nullable', 'regex:/^\d{1,3}:[0-5]\d$/'],
            'model_name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'source_type' => ['required', Rule::in(['local', 'url'])],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'external_url' => ['required', 'string', 'max:2048'],
        ];
        $data = $request->validate($rules);
        if ($data['source_type'] === 'url') $data['source_path'] = $data['external_url'];
        return $data;
    }
}
