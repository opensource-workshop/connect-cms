@extends("manual.common.main_full_norow")

@section('content_main')

<div class="card my-3">
    <div class="card-header text-white bg-secondary">機能一覧</div>
    <div class="card-body">
        <dl class="row mb-0">
            <dd class="col">以下にConnect-CMSの機能一覧を示します。<br /><a href="../pdf/function.pdf" target="_blank">機能概要も記載した機能一覧PDFダウンロード</dd>
        </dl>
    </div>
</div>

@foreach ($methods->groupBy('category') as $category)
<div class="card mb-3">
    <div class="card-header text-white bg-primary"><a href="../{{$category[0]->category}}/index.html" class="text-white">{{ManualCategory::getDescription($category[0]->category)}}</a></div>
    <div class="card-body">
        <dl class="row mb-0">
        @foreach ($category->where('method_name', 'index') as $plugin)
            <dd class="col-md-6 col-lg-3 text-nowrap"><a href="../{{$plugin->category}}/{{$plugin->plugin_name}}/index.html">{{$plugin->plugin_title}}</a></dd>
        @endforeach
        {{-- 開発中などの説明を追加する。 --}}
        {!!$category[0]->getInsertion('category', 'function_foot')!!}
        </dl>
    </div>
</div>
@endforeach

@endsection
