<?php

namespace App\Repositories;

use Plutuss\Facades\MediaAnalyzer;

class Gis
{
    public static function getLatDecimal($gpsLat, $gpsLatRef)
    {
        $degrees = self::exifFractionToFloat($gpsLat[0]);
        $minutes = self::exifFractionToFloat($gpsLat[1]);
        $seconds = self::exifFractionToFloat($gpsLat[2]);

        $decimal = $degrees + $minutes / 60 + $seconds / 3600;

        if('S' === $gpsLatRef) {
            $decimal = $decimal * -1;
        }

        return round($decimal, 6);
    }

    public static function getLngDecimal($gpsLng, $gpsLatRef)
    {
        $degrees = self::exifFractionToFloat($gpsLng[0]);
        $minutes = self::exifFractionToFloat($gpsLng[1]);
        $seconds = self::exifFractionToFloat($gpsLng[2]);

        $decimal = $degrees + $minutes / 60 + $seconds / 3600;

        if('W' === $gpsLatRef) {
            $decimal = $decimal * -1;
        }

        return round($decimal, 6);
    }

    public static function getVideoLat($filename)
    {
        $info = self::getGpsInfo($filename);
        if(isset($info['gps_latitude'])) {
            return  $info['gps_latitude'][0];
        }

        return null;
    }

    public static function getVideoLng($filename)
    {
        $info = self::getGpsInfo($filename);

        if(isset($info['gps_longitude'])) {
            return  $info['gps_longitude'][0];
        }

        return null;
    }

    public static function getVideoCreationDate($filename)
    {
        $info = self::getGpsInfo($filename);

        if(isset($info['creationdate'])) {
            return  $info['creationdate'][0];
        }

        return null;
    }

    private static function getGpsInfo($filename)
    {
        $media = MediaAnalyzer::fromLocalFile(
            path: $filename,
            disk: 'videos',
        );
        dd($media->getNestedValue('comments'));

        $comments = $media->getNestedValue('comments');

        if($comments) {
            return $comments;
        }

        return null;
    }

    private static function exifFractionToFloat($fraction)
    {
        [$num, $den] = explode('/', $fraction);
        return $num / $den;
    }
}
