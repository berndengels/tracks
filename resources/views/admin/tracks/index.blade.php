@extends('layouts.default')

@section('main')
    <div class="row">
        <x-form class="form-floating w-50 m-3" method="post" action="{{ route('admin.tracks.store') }}" enctype="multipart/form-data">
            <div class="row">
                <div class="col">
                    <x-form-input type="file" name="tracks" />
                </div>
                <div class="col">
                    <x-form-submit label="Speichern" />
                </div>
            </div>
        </x-form>
    </div>
    <div class="m-3">
        @if($data->total() > 0)
            {{ $data->links() }}
            <x-table :items="$data"
                     :fields="['ID','Name','Start', 'Ende', 'Points', 'Aktiv']"
                     hasActions isSmall
            >
                @foreach($data as $item)
                    <tr>
                        @bindData($item)
                        <x-td field="id" />
                        <x-td field="name" />
                        <x-td field="start" dateformat="d.m.Y H:i" />
                        <x-td field="end" dateformat="d.m.Y H:i" />
                        <td>{{ $item->trackdata->count() }}</td>
                        <x-td field="active" boolean />
                        <x-action routePrefix="admin.tracks" edit delete />
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

