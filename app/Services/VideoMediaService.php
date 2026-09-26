<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class VideoMediaService
{
    public function generateThumbnail(Video $video): string
    {
        $relative = 'thumbnails/' . $video->id . '.jpg';
        if (Storage::disk('public')->exists($relative)) return 'skipped';
        $source = $this->localPath($video->source_path);
        if ($video->source_type !== 'local' || !$source || !is_readable($source)) return 'failed';
        $ffmpeg = $this->executable(config('video.ffmpeg_path'), 'ffmpeg.exe');
        if (!$ffmpeg) return 'failed';
        Storage::disk('public')->makeDirectory('thumbnails');
        $output = Storage::disk('public')->path($relative);
        [$code, , $error] = $this->run([$ffmpeg, '-y', '-ss', '00:00:01', '-i', $source, '-frames:v', '1', '-vf', 'scale=' . config('video.thumbnail_width', 640) . ':-2', '-q:v', '5', $output], 30);
        if ($code !== 0 || !is_file($output)) {
            Log::error('Batch thumbnail gagal.', ['video_id' => $video->id, 'error' => $error]);
            return 'failed';
        }
        return 'generated';
    }

    public function generateDuration(Video $video): string
    {
        if ($video->duration) return 'skipped';
        $source = $this->localPath($video->source_path);
        $ffprobe = $this->executable(config('video.ffprobe_path'), 'ffprobe.exe');
        if ($video->source_type !== 'local' || !$source || !is_readable($source) || !$ffprobe) return 'failed';
        [$code, $output, $error] = $this->run([$ffprobe, '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', $source], 15);
        $seconds = (float) trim($output);
        if ($code !== 0 || $seconds <= 0) {
            Log::error('Batch duration gagal.', ['video_id' => $video->id, 'error' => $error]);
            return 'failed';
        }
        retry(5, fn () => $video->update(['duration' => $this->formatDuration($seconds)]), 250);
        return 'generated';
    }

    private function executable(?string $configured, string $fallback): ?string
    {
        $candidate = trim((string) ($configured ?: $fallback), " \t\n\r\0\x0B\"");
        if (preg_match('~^[a-z]:[\\/]~i', $candidate) || str_contains($candidate, '\\')) {
            return is_file($candidate) && is_executable($candidate) ? (realpath($candidate) ?: $candidate) : null;
        }
        $process = new Process(['where.exe', $candidate]);
        $process->setTimeout(5);
        $process->run();
        return $process->isSuccessful() ? trim(strtok($process->getOutput(), "\r\n")) : null;
    }

    private function run(array $arguments, int $timeout): array
    {
        $process = new Process($arguments);
        $process->setTimeout($timeout);
        try { $process->run(); } catch (\Throwable $exception) { return [1, '', $exception->getMessage()]; }
        return [$process->getExitCode() ?? 1, $process->getOutput(), $process->getErrorOutput()];
    }

    private function localPath(string $path): ?string
    {
        $path = trim($path);
        if (str_starts_with(strtolower($path), 'file://')) {
            $path = rawurldecode(parse_url($path, PHP_URL_PATH) ?? '');
            if (preg_match('~^/[a-z]:~i', $path)) $path = ltrim($path, '/');
        }
        return realpath(str_replace('/', DIRECTORY_SEPARATOR, $path)) ?: null;
    }

    private function formatDuration(float $seconds): string
    {
        $total = (int) round($seconds); $hours = intdiv($total, 3600); $minutes = intdiv($total % 3600, 60); $remaining = $total % 60;
        return $hours > 0 ? sprintf('%d:%02d:%02d', $hours, $minutes, $remaining) : sprintf('%02d:%02d', $minutes, $remaining);
    }
}
