<?php

namespace App\Console\Commands;

use App\Jobs\GenerateVideoThumbnailJob;
use App\Models\Video;
use App\Services\VideoMediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateVideoThumbnails extends Command
{
    protected $signature = 'videos:generate-thumbnails {--queue : Dispatch one queue job per video}';
    protected $description = 'Generate and cache missing video thumbnails';

    public function handle(VideoMediaService $media): int
    {
        $generated = $skipped = $failed = 0;
        $this->info('Generating thumbnails...');
        foreach (Video::cursor() as $video) {
            if (Storage::disk('public')->exists('thumbnails/' . $video->id . '.jpg')) { $this->line("[SKIP] Video {$video->id}"); $skipped++; continue; }
            if ($this->option('queue')) { GenerateVideoThumbnailJob::dispatch($video->id); $this->line("[QUEUE] Video {$video->id}"); continue; }
            $result = $media->generateThumbnail($video);
            $result === 'generated' ? $generated++ : ($result === 'skipped' ? $skipped++ : $failed++);
            $this->line("[" . strtoupper($result) . "] Video {$video->id}");
        }
        $this->newLine(); $this->info("Completed: Generated: {$generated}, Skipped: {$skipped}, Failed: {$failed}");
        return self::SUCCESS;
    }
}
