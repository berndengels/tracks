@extends('layouts.default')

@section('main')
    <div class="row">
        <x-form class="form-floating w-50 m-3" method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
            <div class="row">
                <div class="col">
                    <x-form-input type="text" name="name" label="Name" />
                    <x-form-input type="file" name="medium" label="Upload" />
                </div>
                <div class="col">
                    <x-form-submit>Speichern</x-form-submit>
                </div>
            </div>
        </x-form>
    </div>
    <div class="m-3">
        @if($data->total() > 0)
            {{ $data->links() }}
            <x-table :items="$data"
                     :fields="['ID','Name','Filename','Type', 'Lat', 'Lng', 'Erstellt']"
                     hasActions isSmall
            >
                @foreach($data as $item)
                    <tr>
                        @bindData($item)
                        <x-td field="id" />
                        <x-td field="name" />
                        <x-td field="filename" />
                        <x-td field="type" />
                        <x-td field="lat" />
                        <x-td field="lng" />
                        <x-td field="created" dateformat="d.m.Y H:i" />
                        <x-action routePrefix="admin.media" edit delete />
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

