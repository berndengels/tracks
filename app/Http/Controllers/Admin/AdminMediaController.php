<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\Media;
use App\Models\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdminMediaController extends Controller
{
    /**x
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Media::with(['track'])->paginate(50);

        return view('admin.media.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMediaRequest $request)
    {
        if($request->file('medium')) {
            $media = Media::create($request->validated());
            $track = Track::select('id')
                ->whereDate('start','>=', $media->created)
                ->whereDate('end','<=', $media->created)
                ->first()
            ;
            if($track) {
                $media->update(['track_id' => $track->id]);
            }
            Cache::clear();
        }

        return redirect()->route('admin.media.index')->with('success', 'Daten erfolgreich angelegt!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $medium)
    {
        $trackOptions = Track::select('id','name','start')
            ->orderBy('start')
            ->get()
            ->keyBy('id')
            ->map(fn($t) => $t->name . ' ' . $t->start->format('Y M d'))
        ;
        return view('admin.media.edit', compact('medium','trackOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMediaRequest $request, Media $medium)
    {
        $medium->update($request->validated());
        Cache::clear();

        return redirect()->route('admin.media.index')->with('success', 'Daten erfolgreich bearbeitet!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $medium)
    {
        switch ($medium->type) {
            case 'image':
                $disk = Storage::disk('image');
                break;
            case 'video':
                $disk = Storage::disk('video');
                break;
        }

        if($disk->exists($medium->filename)) {
            $disk->delete($medium->filename);
        }

        $medium->delete();
        Cache::clear();

        return redirect()->route('admin.media.index')->with('success', 'Daten erfolgreich gelöscht!');
    }

    public function sanitize()
    {
        $disk = Storage::disk('image');
        $files = collect($disk->files())->map(fn($f) => $f !== '.gitignore' ? $f : null)->reject(fn($f) => !$f);
        $dbEntries = Media::select('filename')->whereType('image')->pluck('filename');
        $toRemove = $files->diff($dbEntries);
        $toRemove->each(fn($filename) => $disk->delete($filename));

        $disk = Storage::disk('video');
        $files = collect($disk->files())->map(fn($f) => $f !== '.gitignore' ? $f : null)->reject(fn($f) => !$f);
        $dbEntries = Media::select('filename')->whereType('video')->pluck('filename');
        $toRemove = $files->diff($dbEntries);
        $toRemove->each(fn($filename) => $disk->delete($filename));

        return redirect()->route('admin.media.index')->with('success', 'Daten erfolgreich bereinigt!');
    }
}
