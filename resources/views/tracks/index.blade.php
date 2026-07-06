@extends('layouts.default')

@section('main')
    <div class="w-100">
        <x-map class="w-100" :tracks="$tracks" :bounds="$bounds" />
    </div>
@endsection
