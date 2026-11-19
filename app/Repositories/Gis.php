<?php

namespace App\Repositories;

use App\Models\Track;
//use Illuminate\Database\PDO;
use PDO;
use Illuminate\Support\Facades\DB;
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

    public static function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meter

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public static function trackDistance(Track $track, $type = 'nm')
    {
        $id = $track->id;
        $multiplicator = 'nm' === $type ? 1854 : 1000;

        $query = <<< SQL
            SELECT ROUND(SUM(ST_Distance_Sphere(
                t1.pos,
                t2.pos
            )) / $multiplicator, 1) AS distance
            FROM track_data t1
            JOIN track_data t2 ON t2.id = t1.id - 1
            WHERE t1.track_id = $id AND t2.track_id = $id
            GROUP BY t1.track_id
SQL;
        $result = DB::connection('mysql')
            ->getPdo()
            ->query($query)
//            ->prepare($query)
            ->fetch(PDO::FETCH_NUM)
/*
            ->execute([
                'multiplicator' => 'nm' === $type ? 1854 : 1000,
                'track_id_1'  => $id,
                'track_id_2'  => $id,
            ])
*/
        ;

        return $result[0];
    }
}
