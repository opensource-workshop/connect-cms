@extends("manual.common.main_left_main")

@section('content_main')
    <div class="card">
        <div class="card-header text-white bg-primary">カテゴリトップ</div>
        <div class="card-body">
            <p>
                @if($current_method->category == 'manage')
                    ここでは、Connect-CMS の【管理機能】について説明します。
                @elseif($current_method->category == 'common')
                    ここでは、Connect-CMS の【共通機能】について説明します。
                @else
                    ここでは、Connect-CMS のカテゴリ・メニューについて説明します。
                @endif
            </p>
            <p>
                @if($current_method->category == 'manage')
                    【管理機能】
                @elseif($current_method->category == 'common')
                    【共通機能】
                @else
                    のカテゴリ・メニュー
                @endif
                一覧<br />
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <dl class="row mb-0">
                            @foreach($methods->where('category', $current_method->category)->where('method_name', 'index') as $method)
                                <dd class="col-md-2"><a href="{{$base_path}}{{dirname($method->html_path, 2)}}/index.html">{!!$method->plugin_title!!}</a></dd>
                                <dd class="col-md-10">{!!$method->plugin_desc!!}</dd>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </p>
        </div>
    </div>
@endsection
