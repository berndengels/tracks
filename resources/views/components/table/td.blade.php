@php
use Illuminate\Support\Str;
use Carbon\Carbon;
/**
* @var $date Carbon
 */
@endphp
@if(is_bool($data) && method_exists($model, 'icon'))
    <td class="{{ $field }} align-center justify-content-center align-middle align-content-center @if($style) {{ $style }} @endif" @if($id) id="{{ $id }}" @endif rel="{{ $model->id }}">
        {!! $model->icon($col) !!}
    </td>
@else
    <td class="@if($style){{ $style }} @endif" @if($id) id="{{ $id }}" @endif style="@if($color)color:{{ $data }}; @endif @if($center)text-align:align-center; @endif">
        @if($icon)<i class="link-icon {{ $icon }} mr-1"></i>@endif
        @if($data instanceof Carbon)
            @if($dateformat)
                {!! $data->locale('de_DE')->isoFormat($dateformat) !!}
            @else
                {!! $data->locale('de_DE')->format('d.m.Y') !!}
            @endif
        @else
            @if($link)
                <a href="{{ $link }}" @if($target) target="{{ $target }}"@endif>
            @endif
            @if($short && $short > 0)
                {{ $data ? Str::limit(__($data), $short) : '' }}
            @elseif($text)
                {{ $text }}
            @elseif($currency)
                 {!! number_format($data, 2, ',', '.') ?? '<br>' !!} <span class="ms-1">{{ $currency }}</span>
            @else
                {!! $data ?? '<br>' !!}
            @endif
            @if($link)</a>@endif
        @endif
    </td>
@endif

