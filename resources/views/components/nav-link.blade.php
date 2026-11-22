<a href="{{ $href }}" class="{{ $class }}" @if($target)target="{{ $target }}"@endif>
	@if($icon)
		<i class="{{ $icon }}"></i>
	@endif
	@if($text)
		{{ $text }}
	@else
		{!! trim($slot) !!}
	@endif
</a>
