@extends('layouts.default')

@section('main')
    <div class="row">
        <x-form class="form-floating w-50" method="post" action="{{ route('admin.tracks.store') }}" enctype="multipart/form-data">
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
    <div>
        @if($data->total() > 0)
            {{ $data->links() }}
            <x-table :items="$data"
                     :fields="['ID','Name','Start', 'Ende', 'Points']"
                     hasActions isSmall
            >
                @foreach($data as $item)
                    <tr>
                        @bindData($item)
                        <x-td field="id" />
                        <x-td field="name" />
                        <x-td field="start" />
                        <x-td field="end" />
                        <x-td field="data.count" />
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

