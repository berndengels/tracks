<?php

namespace App\Http\Controllers\Admin;

use App\Models\Track;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\UpdateTrackRequest;
use App\Models\TrackData;
use App\Repositories\Gis;
use Dunn\GpxReader\Facades\Gpx;
use Dunn\GpxReader\DTO\TrackPoint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use MatanYadaev\EloquentSpatial\Objects\Point;

class AdminTrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Track::orderBy('start');
        $totalKM = with(clone $query)->get('km')->pluck('km')->sum();
        $totalNM = with(clone $query)->get('nm')->pluck('nm')->sum();
        $data = $query->paginate($this->paginationLimit);

        $query->get()->each(function (Track $t) {
            if(!$t->km || !$t->nm) {
                $points = TrackData::whereTrackId($t->id)
                    ->orderBy('datetime')
                    ->get();

                $total = 0;

                for ($i = 1; $i < count($points); $i++) {
                    $total += Gis::distance(
                        $points[$i-1]->lat,
                        $points[$i-1]->lng,
                        $points[$i]->lat,
                        $points[$i]->lng,
                    );
                }

                $km = round($total / 1000, 1);
                $nm = round($total / 1852, 1);

                $t->update([
                    'km'    => $km,
                    'nm'    => $nm,
                ]);
            }
        });

        return view('admin.tracks.index', compact('data','totalKM', 'totalNM'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrackRequest $request)
    {
        try {
            /**
             * @var $file UploadedFile
             */
            $file = $request->validated('tracks');
            setlocale(LC_CTYPE, 'de_DE');

            if($file) {
                $modulo = config('tracks.track.import.modulo');
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
//                            $track = Track::where('start','=', $segment->extensions->navionics_start_time)->first();

                            if($track) {
                                $points = collect($segment->points)
                                    ->map(function(TrackPoint $p) use ($modulo, $track) {
                                        $stamp = $p->time->getTimestamp();
                                        if($p->extensions->navionics_speed > 0 && $p->latitude && $p->longitude && 0 === $stamp % $modulo) {
                                            return [
                                                'track_id'  => $track->id,
                                                'lat'   => $p->latitude,
                                                'lng'   => $p->longitude,
                                                'pos'   => new Point($p->latitude, $p->longitude),
                                                'datetime'  => $p->time->format('Y-m-d H:i:s'),
                                                'speed' => $p->extensions->navionics_speed,
                                                'stamp' => $stamp,
                                            ];
                                        }
                                        return null;
                                    })->reject(fn($p) => !$p);

                                $points->each(fn(array $c, $idx) => $track->trackdata()->create($c));
                            }
                        }
                    }
                    Cache::clear();
                }
            }

            return redirect()->route('admin.tracks.index')->with('success', 'Daten erfolgreich angelegt!');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(Track $track) {
        $track->load(['trackdata']);
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

    public function toggle(Request $request, Track $track)
    {
        if($request->isXmlHttpRequest()) {
            $attribute  = $request->post('attribute');
            $value      = (bool) $request->post('value');
            $track->update([$attribute => $value]);
            $track->refresh();
            return response()->json($track);
        }

        return response()->json(['error' => 'no ajax request']);
    }
}
