@extends('layouts.default')

@section('main')
	<div class="container-fluid w-100 m-3">
		<x-btn-back route="{{ route('admin.tracks.index') }}" />
		<div class="row">
			<div class="col-4">
				<x-form method="post" class="d-inline-flex" :action="route('admin.tracks.update', $track)" class="w-100 mt-3">
					@method('put')
					@bind($track)
					<x-form-input floating name="name" label="Name" />
                    <x-form-checkbox floating name="active" label="Aktiv" />
					@endbind
                    <x-form-submit class="btn-sm btn-primary" icon="fas fa-save">Speichern</x-form-submit>
				</x-form>
			</div>
            <div class="col-8" id="adminMap"></div>
		</div>
	</div>
@endsection
