@extends('layouts.default')
@push('styles')
<style>
    #msg {
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        padding: 10px;
        background-color: #fff;
        color: #6c757d;
        border: 1px solid #000;
        z-index: 1000;
    }
</style>
@endpush

@section('main')
    <div class="w-100">
        <x-map class="w-100"
               :tracks="$tracks"
               :points="$points"
               :bounds="$bounds"
               :duration="$duration"
        />
        {{--
        <!--div id="tracks"
             data-bounds="{{ $bounds }}"
             data-north-east="{{ $northEast }}"
             data-south-west="{{ $southWest }}"
        /-->
        --}}
    </div>
@endsection
