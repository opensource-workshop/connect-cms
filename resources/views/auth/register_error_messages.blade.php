{{--
 * エラーメッセージのテンプレート。
--}}
@extends('layouts.app')

@section('content')
<main class="container mt-3" role="main">
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($error_messages as $error_message)
                <p class="text-center cc_margin_bottom_0">{!! nl2br(e($error_message)) !!}</p>
            @endforeach
        </div>
    </div>
</main>
@endsection
