<x-form action="{{ $route }}"
        class="d-inline-block m-0 p-0">
    @method('delete')
    <x-form-submit title="Löschen" inline class="mt-0 btn btn-sm btn-danger delSoft">
        <i class="fas fa-trash-alt"></i>
        <span class="d-none d-md-inline-block">Löschen</span>
    </x-form-submit>
</x-form>
