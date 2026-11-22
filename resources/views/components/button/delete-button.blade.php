<x-form action="{{ $route }}"
        class="d-inline-block m-0 p-0">
    @method('delete')
    <x-form-submit icon="fas fa-trash-alt" title="Löschen" inline class="mt-0 btn btn-sm btn-danger delSoft">
        <span class="d-none d-md-inline-block">Löschen</span>
    </x-form-submit>
</x-form>
