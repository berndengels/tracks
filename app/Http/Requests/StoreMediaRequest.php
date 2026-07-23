<?php

namespace App\Http\Requests;

use App\Repositories\Gis;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreMediaRequest extends AdminRequest
{
    protected function prepareForValidation()
    {
        $videoDisk = Storage::disk('videos');
        $imageDisk = Storage::disk('images');
        $file = $this->file('medium');
        $type = 'image';

        if($file) {
            $filename = $file->hashName();
            switch ($file->getMimeType()) {
                case 'video/quicktime':
                case 'video/mp4':
                case 'video/webm':
                case 'video/ogg':
                case 'video/mpeg':
                case 'video/x-matroska':
                    $type = 'video';
                    $file->move($videoDisk->path(''), $filename);
                    $lat = Gis::getVideoLat($filename);
                    $lng = Gis::getVideoLng($filename);
                    $created = Gis::getVideoCreationDate($filename);
                    $this->request->set('lat', $lat);
                    $this->request->set('lng', $lng);
                    $this->request->set('created', $created);
                    $this->request->set('pos', new Point($lat, $lng));
                    break;
                case 'image/jpeg':
                case 'image/png':
                    $type = 'image';
                    $file->move($imageDisk->path(''), $filename);
                    $manager = new ImageManager(new Driver());

                    $image = $manager->read($imageDisk->path($filename));
                    $gps = $image->exif('GPS');
                    $exif = $image->exif('EXIF');
                    $created = $exif['DateTimeOriginal'];
                    $lat = Gis::getLatDecimal($gps['GPSLatitude'], $gps['GPSLatitudeRef']);
                    $lng = Gis::getLngDecimal($gps['GPSLongitude'], $gps['GPSLongitudeRef']);
                    $this->request->set('lat', $lat);
                    $this->request->set('lng', $lng);
                    $this->request->set('pos', new Point($lat, $lng));
                    $this->request->set('created', $created);
                    break;
            }
            $this->request->set('filename', $filename);
            $this->request->set('type', $type);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => 'required|unique:media,name',
//            'filename'  => 'file|unique:media,filename',
            'filename'  => '',
            'type'  => '',
            'created' => '',
            'lat'  => '',
            'lng'  => '',
            'pos'   => '',
            'medium' => '',
        ];
    }
}
