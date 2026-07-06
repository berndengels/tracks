@extends('layouts.default')

@section('main')
	<div class="container-fluid w-100 mt-3">
		<x-btn-back route="{{ route('admin.tracks.index') }}" />
		<div class="row">
			<div class="col-sm-12 col-lg-4">
				<x-form method="post" :action="route('admin.tracks.update', $track)" class="w-100 mt-3">
					@method('put')
					@bind($track)
					<x-form-input floating name="name" label="Name" />
					@endbind
					<div class="mt-3">
						<x-form-submit class="btn-sm btn-primary" icon="fas fa-save">Speichern</x-form-submit>
					</div>
				</x-form>
			</div>
		</div>
	</div>
@endsection
