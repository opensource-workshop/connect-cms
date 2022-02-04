@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">{{$current_method->plugin_title}} - {{$current_method->method_title}}</div>
        <div class="card-body">
            <p>{!!nl2br($current_method->method_desc)!!}</p>
            <p>{!!$current_method->method_detail!!}</p>
        </div>
    </div>

    @if ($current_method->img_args)
    <div class="card mt-3">
        <div class="card-header text-white bg-primary">画面</div>
        <div class="card-body">
            @foreach($current_method->getImgArgs() as $img_arg)
                <p>
                    @if($img_arg["name"])
                        【{{$img_arg["name"]}}】<br />
                    @elseif(count($current_method->getImgArgs()) > 1)
                        【画像：{{$loop->iteration}}】<br />
                    @endif
                    <img src="./images/{{basename($img_arg["path"])}}.png" class="img-fluid img-manual"
                        @if ($img_arg["style"]) style="{{$img_arg["style"]}}" @endif
                    >

                    @if($img_arg["comment"])
                        <div class="card bg-light">
                            <div class="card-body">
                                {!!$img_arg["comment"]!!}
                            </div>
                        </div>
                    @endif
                </p>
            @endforeach
            {!!$current_method->getInsertion($level, 'foot')!!}
        </div>
    </div>
    @endif
@endsection
