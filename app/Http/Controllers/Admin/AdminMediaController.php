<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdminMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Media::paginate(50);

        return view('admin.media.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMediaRequest $request)
    {
        if($request->file('medium')) {
            Media::create($request->validated());
            Cache::clear();
        }

        return redirect()->route('admin.media.index')->with('success', 'Daten erfolgreich angelegt!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $medium)
    {
        return view('admin.media.edit', compact('medium'));
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
                $disk = Storage::disk('images');
                break;
            case 'video':
                $disk = Storage::disk('videos');
                break;
        }

        if($disk->exists($medium->filename)) {
            $disk->delete($medium->filename);
        }

        $medium->delete();
        Cache::clear();

        return redirect()->route('admin.media.index')->with('success', 'Daten erfolgreich gelöscht!');
    }
}
