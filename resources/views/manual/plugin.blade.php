@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">{{$current_method->plugin_title}}・プラグイン</div>
        <div class="card-body">
            @if ($current_method->hasMp4(2, '_mp4'))
                <div class="row">
                    <div class="col-lg-4 p-0">
                        <div class="embed-responsive embed-responsive-16by9">
                            <video src="../../{{$current_method->getMp4Path(2, '_mp4')}}" class="embed-responsive-item" controls></video>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <p>ここでは、Connect-CMS の【{{$current_method->plugin_title}}】メニューについて説明します。</p>
                    </div>
                </div>
            @else
                <p>ここでは、Connect-CMS の【{{$current_method->plugin_title}}】メニューについて説明します。</p>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header text-white bg-primary">プラグイン概要</div>
        <div class="card-body">
            {!!$current_method->plugin_desc!!}
            {!!$current_method->getInsertion($level, 'desc', '<p>', '</p>')!!}
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header text-white bg-primary">機能一覧</div>
        <div class="card-body">
            <dl class="row mb-0">
                @foreach($methods->where('category', $current_method->category)->where('plugin_name', $current_method->plugin_name) as $method)
                    <dt class="col-md-2"><a href="{{$base_path}}{{$method->html_path}}">{!!$method->method_title!!}</a></dt>
                    <dd class="col-md-10">{!!$method->method_desc!!}</dd>
                @endforeach
            </dl>
            {!!$current_method->getInsertion($level, 'foot')!!}
        </div>
    </div>
@endsection
