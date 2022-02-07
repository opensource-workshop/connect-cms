@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">プラグイン・トップ</div>
        <div class="card-body">
            <p>ここでは、Connect-CMS の【{{$current_method->plugin_title}}】メニューについて説明します。</p>
            <p>【{{$current_method->plugin_title}}】概要<br />
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        {!!$current_method->plugin_desc!!}
                        {!!$current_method->getInsertion($level, 'desc', '<p>', '</p>')!!}
                    </div>
                </div>
            </p>
            <p>【{{$current_method->plugin_title}}】機能一覧<br />
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <dl class="row mb-0">
                            @foreach($methods->where('category', $current_method->category)->where('plugin_name', $current_method->plugin_name) as $method)
                                <dt class="col-md-2"><a href="{{$base_path}}{{$method->html_path}}">{!!$method->method_title!!}</a></dt>
                                <dd class="col-md-10">{!!$method->method_desc!!}</dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </p>
            {!!$current_method->getInsertion($level, 'foot')!!}
        </div>
    </div>
@endsection
