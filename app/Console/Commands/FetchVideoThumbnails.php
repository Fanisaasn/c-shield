<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Support\VideoThumbnail;
use Illuminate\Console\Command;

class FetchVideoThumbnails extends Command
{
    protected $signature = 'videos:fetch-thumbnails';

    protected $description = 'Fill in missing video thumbnails from their YouTube/Instagram links';

    public function handle(): int
    {
        $fetched = 0;
        $videos = Video::query()->whereNull('thumbnail')->whereNotNull('video_url')->get();

        foreach ($videos as $video) {
            $path = VideoThumbnail::fetch($video->video_url);

            if ($path === null) {
                $this->warn("No thumbnail for video #{$video->id}: {$video->video_url}");

                continue;
            }

            $video->update(['thumbnail' => $path]);
            $fetched++;
        }

        $this->info("Fetched {$fetched} of {$videos->count()} missing thumbnails.");

        return self::SUCCESS;
    }
}
