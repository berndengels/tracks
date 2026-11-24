@extends('layouts.default')

@section('main')
    <x-form class="form-floating m-3" method="post" action="{{ route('admin.route-planners.store') }}" enctype="multipart/form-data">
        <div class="row w-full">
            <div class="col-sm-12 col-lg-6">
                <x-form-input floating class="w-full" type="file" name="tracks" label="GPX-Daten" />
            </div>
            <div class="col-sm-12 col-lg-6">
                <x-form-submit floating label="Speichern" />
            </div>
        </div>
    </x-form>
    <div class="m-3">
        @if($data->total() > 0)
            {{ $data->links() }}
            <x-table :items="$data"
                     :fields="['ID','Name']"
                     hasActions isSmall
            >
                @foreach($data as $item)
                    <tr>
                        @bindData($item)
                        <x-td field="id" />
                        <x-td field="name" />
                        <x-action routePrefix="admin.route-planners" edit delete />
                        @endBindData
                    </tr>
                @endforeach
            </x-table>
            {{ $data->links() }}
        @else
            <div class="m-3">
                <h4>Keine Daten vorhanden</h4>
            </div>
        @endif
    </div>
@endsection
