@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">{{$current_method->plugin_title}} - {{$current_method->method_title}}</div>
        <div class="card-body">
            @if ($current_method->hasMp4())
                <div class="row">
                    <div class="col-lg-4 p-0">
                        <div class="embed-responsive embed-responsive-16by9">
                            @if ($current_method->hasPoster())
                            <video src="../../../{{$current_method->getMp4Path()}}"
                                   class="embed-responsive-item"
                                   controls>
                            </video>
                            @else
                            <video src="../../../{{$current_method->getMp4Path()}}"
                                   class="embed-responsive-item"
                                   poster="../../../{{$current_method->getPosterPath()}}"
                                   controls>
                            </video>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <p>{!!nl2br($current_method->method_desc)!!}</p>
                        <p>{!!$current_method->method_detail!!}</p>
                        {!!$current_method->getInsertion($level, 'desc', '<p>', '</p>')!!}
                    </div>
                </div>
            @else
                <p>{!!nl2br($current_method->method_desc)!!}</p>
                <p>{!!$current_method->method_detail!!}</p>
                {!!$current_method->getInsertion($level, 'desc', '<p>', '</p>')!!}
            @endif
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
{{-- 別フォルダから画像を持ってくる場合もあるので、ルートまで遡ってのパスにする。
                    <img src="./images/{{basename($img_arg["path"])}}.png" class="img-fluid img-manual"
--}}
                    @php
                        // ここでは、まだ、manual 側に画像ができていないので、screenshot を参照。
                        $image_info = getimagesize(\Storage::disk("screenshot")->path($img_arg["path"] . ".png"));
                        $width = $image_info[0];
                        if ($width > 980) {
                            $width = 980;
                        }
                    @endphp

                    <img src="../../../{{$img_arg["path"]}}.png" class="img-fluid img-manual" style="max-width:{{$width}}px;"
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
        </div>
    </div>
    @endif
    {!!$current_method->getInsertion($level, 'foot')!!}
@endsection
