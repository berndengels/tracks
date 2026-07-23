<?php

namespace App\Http\Controllers\Admin;

use App\Models\Track;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\UpdateTrackRequest;
use Dunn\GpxReader\Facades\Gpx;
use Dunn\GpxReader\DTO\TrackPoint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminTrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Track::orderBy('start')->paginate($this->paginationLimit);

        return view('admin.tracks.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrackRequest $request)
    {
        /**
         * @var $file UploadedFile
         */
        $file = $request->validated('tracks');
        setlocale(LC_CTYPE, 'de_DE');

        if($file) {
            $content = trim($file->getContent());
            $gpx = Gpx::parseFromString($content);

            if($gpx->tracks) {
                foreach ($gpx->tracks as $item) {
                    foreach($item->segments as $segment) {
                        $track = Track::create([
                            'name'  => $item->name,
                            'start' => $segment->extensions->navionics_start_time,
                            'end'   => $segment->extensions->navionics_end_time,
                        ]);

                        if($track) {
                            $points = collect($segment->points)
                                ->map(fn(TrackPoint $p) => $p->extensions->navionics_speed > 0 && $p->latitude && $p->longitude ? [
                                    'track_id'  => $track->id,
                                    'lat'   => $p->latitude,
                                    'lng'   => $p->longitude,
                                    'datetime'  => $p->time,
                                    'speed' => $p->extensions->navionics_speed,
                                ] : null)->reject(fn($p) => !$p);

                            $points
                                ->chunk(1000)
                                ->each(fn(Collection $c) => $track->trackdata()->insertOrIgnore($c->toArray()));
                        }
                    }
                }
                Cache::clear();
            }
        }

        return redirect()->route('admin.tracks.index')->with('success', 'Daten erfolgreich angelegt!');
    }

    public function edit(Track $track) {
        return view('admin.tracks.edit', compact('track'));
    }

    public function update(UpdateTrackRequest $request, Track $track)
    {
        $track->update($request->validated());
        Cache::clear();

        return redirect()->route('admin.tracks.index')->with('success', 'Daten erfolgreich bearbeitet!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Track $track)
    {
        $track->delete();
        Cache::clear();

        return redirect()->route('admin.tracks.index')->with('success', 'Daten erfolgreich gelöscht!');
    }
}
