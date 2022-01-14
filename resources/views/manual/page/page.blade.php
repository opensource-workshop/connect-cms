@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">{{$method->method_title}}</div>
        <div class="card-body">
            <p>{!!$method->plugin_desc!!}</p>
        </div>
    </div>
@endsection
