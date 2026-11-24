@extends('layouts.default')

@section('main')
    <div id="app">
        <sailing-tracks-map
            :north-east='{!! $northEast !!}'
            :south-west='{!! $southWest !!}'
            :center='{!! $center !!}'
            :year-options='{!! $yearOptions !!}'
            :zoom='{!! $zoom !!}'
            :max-zoom='{!! $maxZoom !!}'
            :km='{!! $km !!}'
            :nm='{!! $nm !!}'
        ></sailing-tracks-map>
    </div>
@endsection
