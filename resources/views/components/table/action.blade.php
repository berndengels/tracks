
<td class="table-action">
    @if($edit)
        <x-btn-edit route="{{ route($routePrefix . '.edit', $model->id) }}" />
    @endif
    @if($delete)
        <x-btn-delete route="{{ route($routePrefix . '.destroy', $model->id) }}" />
    @endif
</td>
