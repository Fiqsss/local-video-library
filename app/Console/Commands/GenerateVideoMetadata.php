<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\VideoMediaService;
use Illuminate\Console\Command;

class GenerateVideoMetadata extends Command
{
    protected $signature = 'videos:generate-metadata';
    protected $description = 'Generate and cache missing video durations';

    public function handle(VideoMediaService $media): int
    {
        $generated = $skipped = $failed = 0;
        foreach (Video::cursor() as $video) {
            try {
                $result = $media->generateDuration($video);
            } catch (\Throwable $exception) {
                $result = 'failed';
                $this->error("[ERROR] Video {$video->id}: {$exception->getMessage()}");
            }
            $result === 'generated' ? $generated++ : ($result === 'skipped' ? $skipped++ : $failed++);
            $this->line("[" . strtoupper($result) . "] Video {$video->id}");
        }
        $this->info("Completed: Generated: {$generated}, Skipped: {$skipped}, Failed: {$failed}");
        return self::SUCCESS;
    }
}
