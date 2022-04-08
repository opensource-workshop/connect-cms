@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">{{ManualCategory::getDescription($current_method->category)}}・カテゴリ</div>
        <div class="card-body">
            @if ($current_method->hasMp4(3, '_mp4'))
                <div class="row">
                    <div class="col-lg-4 p-0">
                        <div class="embed-responsive embed-responsive-16by9">
                            <video src="../{{$current_method->getMp4Path(3, '_mp4')}}" class="embed-responsive-item" controls></video>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <p>ここでは、Connect-CMS の【{{ManualCategory::getDescription($current_method->category)}}】カテゴリについて説明します。</p>
                    </div>
                </div>
            @else
                <p>ここでは、Connect-CMS の【{{ManualCategory::getDescription($current_method->category)}}】カテゴリについて説明します。</p>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header text-white bg-primary">プラグイン一覧</div>
        <div class="card-body">
            <dl class="row mb-0">
                @foreach($methods->where('category', $current_method->category)->where('method_name', 'index') as $method)
                    <dt class="col-md-2"><a href="{{$base_path}}{{dirname($method->html_path, 2)}}/index.html">{!!$method->plugin_title!!}</a></dt>
                    <dd class="col-md-10">{!!$method->plugin_desc!!}</dd>
                @endforeach
            </dl>
        </div>
    </div>
@endsection
