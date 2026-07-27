@extends('layouts.default')

@section('main')
    <div class="w-100">
        <x-map class="w-100"
               :tracks="$tracks"
               :points="$points"
               :bounds="$bounds"
               :media="$media"
               :km="$km"
               :nm="$nm"
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
