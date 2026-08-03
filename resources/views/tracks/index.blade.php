@extends('layouts.default')

@push('styles')
    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <link rel="stylesheet" href="leaflet.markercluster/MarkerCluster.css" />
    <link rel="stylesheet" href="leaflet.markercluster/MarkerCluster.Default.css" />
    <style>
        marker-cluster-small {
            background-color: rgba(255, 226, 140, 0.6);
        }
        .marker-cluster-small div {
            background-color: rgba(255, 0, 0, 0.8);
            color: #fff;
        }
    </style>
@endpush

@push('scripts')
    <script src="leaflet/leaflet.js"></script>
    <script src="leaflet.markercluster/leaflet.markercluster.js"></script>
    <script src="https://wurfl.io/wurfl.js"></script>
@endpush

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
