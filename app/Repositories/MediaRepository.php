<?php

namespace App\Repositories;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Coordinate\Dimension;
class MediaRepository
{
    public static function convertToMp4($input, $output, $with = 640)
    {
        $highBitrate = (new X264)->setKiloBitrate(1000);

        FFMpeg::open($input)
            ->export()
            ->toDisk('videos')
            ->inFormat(new X264)
//            ->resize(640, 480)
            ->addFormat($highBitrate, function ($media) {
                $media->addFilter(function ($filters, $in, $out) {
                    $filters->custom($in, 'scale=640:480', $out); // $in, $parameters, $out
                });
            })
            ->addFilter(['-movflags','use_metadata_tags','-map_metadata 0'])
            ->save($output);
        ;
    }
}
