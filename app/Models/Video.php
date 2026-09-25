<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Video extends Model
{
    protected $fillable = ['title', 'duration', 'model_name', 'category_id', 'source_type', 'source_path', 'thumbnail_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_video');
    }

    public function models(): BelongsToMany
    {
        return $this->belongsToMany(ModelName::class, 'model_video', 'video_id', 'model_name_id');
    }

    public function getPlaybackUrlAttribute(): string
    {
        $path = trim((string) $this->source_path);

        if ($this->source_type === 'local' && (preg_match('~^[a-z]:[\\\\/]~i', $path) || str_starts_with(strtolower($path), 'file://'))) {
            return route('videos.stream', $this);
        }

        if ($this->source_type === 'local' && !preg_match('~^https?://~i', $path)) {
            if (!preg_match('~^[a-z]:[\\\\/]~i', $path) && !str_starts_with($path, '/')) {
                return route('videos.stream', $this);
            }
            $publicPath = str_replace('\\', '/', public_path());
            $normalizedPath = str_replace('\\', '/', $path);
            if (str_starts_with(strtolower($normalizedPath), strtolower($publicPath) . '/')) {
                $path = ltrim(substr($normalizedPath, strlen($publicPath)), '/');
            }
            return url('/' . ltrim(str_replace('\\', '/', $path), '/'));
        }

        return $path;
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if ($this->source_type !== 'url') return null;
        $url = trim($this->source_path);
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $id = null;
        if (in_array($host, ['youtu.be', 'www.youtu.be'])) {
            $id = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'])) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            if ($path === '/watch') parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
            if ($path === '/watch') $id = $query['v'] ?? null;
            elseif (preg_match('~^/(?:embed|shorts|live)/([^/?]+)~', $path, $m)) $id = $m[1];
        }
        return $id ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($id) : null;
    }
}
