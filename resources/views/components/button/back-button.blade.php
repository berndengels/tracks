<a @if($route)
        href="{{ $route }}"
    @else
        href="javascript:back()"
    @endif
    class="btn btn-sm btn-outline-primary {{ $class }}" title="Zurück"><i class="fas fa-backward"></i>
    <span class="d-none d-md-inline-block">zurück</span></a>
