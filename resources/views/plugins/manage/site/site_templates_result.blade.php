{{-- サイトひな型の適用結果 --}}
@extends('plugins.manage.manage')

@section('manage_content')
<div class="card">
    <div class="card-header p-0">
        @include('plugins.manage.site.site_manage_tab')
    </div>
    <div class="card-body">
        <h5 class="border-bottom pb-2 mb-3">サイトひな型の適用結果</h5>
        <div class="alert alert-success" role="status">
            <i class="fas fa-check" aria-hidden="true"></i> {{ $template_name }}を適用しました。
        </div>

        <dl class="row">
            <dt class="col-md-3">適用日時</dt><dd class="col-md-9">{{ $application->applied_at }}</dd>
            <dt class="col-md-3">実行者</dt><dd class="col-md-9 text-break">{{ $application->created_name }}</dd>
        </dl>

        <div class="alert alert-info" role="note">
            <h6 class="alert-heading">作成後の編集</h6>
            <p class="mb-2">ページ名はページ管理から変更できます。</p>
            <p class="mb-0">各コンテンツはそれぞれのページから編集してください。</p>
        </div>
        <div class="text-center">
            <a href="{{ url('/manage/site/siteTemplates') }}" class="btn btn-secondary mr-2 mb-2"><i class="fas fa-list" aria-hidden="true"></i> 一覧へ戻る</a>
            <a href="{{ url('/manage/page') }}" class="btn btn-primary mr-2 mb-2"><i class="far fa-edit" aria-hidden="true"></i> ページ管理</a>
            <a href="{{ url('/') }}" class="btn btn-primary mb-2"><i class="fas fa-home" aria-hidden="true"></i> トップページ</a>
        </div>
    </div>
</div>
@endsection
