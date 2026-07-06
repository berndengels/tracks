<x-nav-link
        href="{{ $route }}"
        icon="fas fa-eye"
        class="btn btn-sm btn-outline-primary"
>
    @if($slot)
        <span class="d-none d-md-inline-block">{{ $slot }}</span>
    @else
        <span class="d-none d-md-inline-block">Show</span>
    @endif
</x-nav-link>
