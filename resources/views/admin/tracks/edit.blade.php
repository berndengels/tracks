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
	<div class="container-fluid w-100 m-3">
		<x-btn-back route="{{ route('admin.tracks.index') }}" />
		<div class="row">
			<div class="col-sm-12 col-lg-6">
				<x-form method="post" class="d-inline-flex" :action="route('admin.tracks.update', $track)" class="w-100 mt-3">
					@method('put')
					@bind($track)
					<x-form-input floating name="name" label="Name" />
                    <x-form-checkbox floating name="active" label="Aktiv" />
					@endbind
                    <x-form-submit class="btn-sm btn-primary" icon="fas fa-save">Speichern</x-form-submit>
				</x-form>
			</div>
            <!--x-admin-map :track="$track" /-->
		</div>
	</div>
@endsection
