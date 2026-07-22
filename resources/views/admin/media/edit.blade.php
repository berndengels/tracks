@extends('layouts.default')

@section('main')
	<div class="container-fluid w-100 m-3">
		<x-btn-back route="{{ route('admin.media.index') }}" />
		<div class="row">
			<div class="col">
				<x-form method="post" class="d-inline-flex" :action="route('admin.media.update', $medium)" class="w-100 mt-3">
					@method('put')
					@bind($medium)
					<x-form-input floating name="name" label="Name" />
					@endbind
                    <x-form-submit class="btn-sm btn-primary" icon="fas fa-save">Speichern</x-form-submit>
				</x-form>
			</div>
		</div>
	</div>
@endsection
