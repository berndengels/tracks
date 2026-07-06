@extends('layouts.default')

@section('main')
    <div class="container w-100">
        <h3>Sailing Tracks</h3>
        <x-map class="w-100" :tracks="$tracks" :bounds="$bounds" />
    </div>
@endsection
