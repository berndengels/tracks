@extends('layouts.default')

@section('main')
    <div id="tracks"
         data-init-north-east="{{ $northEast }}"
         data-init-south-west="{{ $southWest }}"
         data-init-center="{{ $center }}"
         data-init-year-options="{{ $yearOptions }}"
         data-zoom="{{ $zoom }}"
         data-max-zoom="{{ $maxZoom }}"
         data-km="{{ $km }}"
         data-nm="{{ $nm }}"
    />
@endsection
