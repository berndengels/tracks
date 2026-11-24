@extends('layouts.default')

@section('main')
	<div class="container-fluid w-100 m-3">
		<x-btn-back route="{{ route('admin.route-planners.index') }}" />
		<div class="row">
			<div class="col-sm-12 col-lg-6">
				<x-form method="post" class="d-inline-flex" :action="route('admin.route-planners.update', $routePlanner)" class="w-100 mt-3">
					@method('put')
					@bind($routePlanner)
					<x-form-input floating name="name" label="Name" />
                    <x-form-textarea name="desc" label="Beschreibung" />
					@endbind
                    <x-form-submit class="btn-sm btn-primary" icon="fas fa-save">Speichern</x-form-submit>
				</x-form>
			</div>
		</div>
        <div id="app">
            <SailingTripPlanner
                :init-north-east='{!! $northEast !!}'
                :init-south-west='{!! $southWest !!}'
                :init-center='{!! $center !!}'
                :zoom='{!! $zoom !!}'
                :max-zoom='{!! $maxZoom !!}'
            ></SailingTripPlanner>
        </div>
    </div>
@endsection
