@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">カテゴリトップ</div>
        <div class="card-body">
            <p>
                ここでは、Connect-CMS の【{{ManualCategory::getDescription($current_method->category)}}】機能について説明します。
            </p>
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        @foreach($methods->where('category', $current_method->category)->where('method_name', 'index') as $method)
                            <dt class="col-md-2"><a href="{{$base_path}}{{dirname($method->html_path, 2)}}/index.html">{!!$method->plugin_title!!}</a></dt>
                            <dd class="col-md-10">{!!$method->plugin_desc!!}</dd>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
