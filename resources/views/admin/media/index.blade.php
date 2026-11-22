@extends('layouts.default')

@section('main')
    <x-form class="form-floatingm-3" method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <x-form-input floating type="text" name="name" label="Name" />
                <x-form-input floating type="file" name="medium" label="Upload" />
            </div>
            <div class="col-sm-12 col-lg-6">
                <x-form-submit floating label="Speichern" />
            </div>
        </div>
    </x-form>
    <div class="m-3">
        <a class="link-danger" href="{{ route('admin.media.sanitize') }}">Media Files bereinigen</a>
    </div>
    <div class="m-3">
        @if($data->total() > 0)
            {{ $data->links() }}
            <x-table :items="$data"
                     :fields="['ID','Track:md','Name','Filename:md','Type', 'Lat:md', 'Lng:md', 'Exist', 'Erstellt:md']"
                     hasActions isSmall
            >
                @foreach($data as $item)
                    <tr>
                        @bindData($item)
                        <x-td field="id" />
                        <x-td field="track.name:md" />
                        <x-td field="name" />
                        <x-td field="filename:md" />
                        <x-td field="type" />
                        <x-td field="lat:md" />
                        <x-td field="lng:md" />
                        <x-td field="fileExist" bool />
                        <x-td field="created:md" dateformat="d.m.Y H:i" />
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

