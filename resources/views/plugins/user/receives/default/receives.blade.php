{{--
 * データ収集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データ収集プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- モデレータ（他ユーザの記事も更新）の場合のみ、表示 --}}
@can("role_article")

    <dl class="row">
        <dt class="col-sm-2">件数</dt>
        <dd class="col-sm-10">{{$receives_count}}件</dd>
        <dt class="col-sm-2">最終登録日時</dt>
        <dd class="col-sm-10">@isset($receives_last){{$receives_last->created_at}}@endif</dd>
    </dl>

    <form action="/download/plugin/receives/downloadCsv/{{$page->id}}/{{$frame_id}}/{{$receive_frame->receive_id}}" method="POST" class="">
        {{ csrf_field() }}
        <button type="submit" class="btn btn-success btn-sm">
            <i class="fas fa-file-download"></i> ダウンロード
        </button>
    </form>
@else

    <div class="card">
        <div class="card-header alert-danger">
            権限チェック
        </div>
        <div class="card-body">
            <p class="card-text">この機能はモデレータ以上の権限が必要です。</p>
        </div>
    </div>
@endcan

@endsection
