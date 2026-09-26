<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\VideoMediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateVideoThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public int $videoId) {}

    public function handle(VideoMediaService $media): void
    {
        $video = Video::find($this->videoId);
        if ($video) $media->generateThumbnail($video);
    }
}
