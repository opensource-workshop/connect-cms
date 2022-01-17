@extends("manual.common.main_left_main")

@section('content_main')
    @if ($current_method->method_name == 'index')
        <div class="card mb-3">
            <div class="card-header text-white bg-primary">{{$current_method->plugin_title}}</div>
            <div class="card-body">
                <p>{!!$current_method->plugin_desc!!}</p>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header text-white bg-primary">{{$current_method->plugin_title}} - {{$current_method->method_title}}</div>
        <div class="card-body">
            <p>{!!nl2br($current_method->method_desc)!!}</p>
            <p>{!!$current_method->method_detail!!}</p>
        </div>
    </div>

{{--
    <div class="card mt-3">
        <div class="card-header text-white bg-primary">詳細説明</div>
        <div class="card-body">
            <p>{!!$current_method->method_detail!!}</p>
        </div>
    </div>
--}}

    @if ($current_method->img_paths)
    <div class="card mt-3">
        <div class="card-header text-white bg-primary">画面</div>
        <div class="card-body">
            @foreach($current_method->getImgPathArray() as $img_path)
                <p>
                    @if(count($current_method->getImgPathArray()) > 1) 【画像：{{$loop->iteration}}】<br />@endif
                    <img src="./images/{{basename($img_path)}}.png" class="img-fluid img-thumbnail">
                </p>
            @endforeach
        </div>
    </div>
    @endif
@endsection
