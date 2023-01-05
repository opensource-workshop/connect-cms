{{-- €”Ô‚Ì•ÒW --}}
@if (empty($number))
    {!!$txt!!}
@else
    @if ($level == 'category')
        {{$number['category']}} {!!$txt!!}
    @elseif ($level == 'plugin')
        {{$number['category']}}.{{$number['plugin']}} {!!$txt!!}
    @elseif ($level == 'method')
        {{$number['category']}}.{{$number['plugin']}}.{{$number['spec']}} {!!$txt!!}
    @else
        {!!$txt!!}
    @endif
@endif
