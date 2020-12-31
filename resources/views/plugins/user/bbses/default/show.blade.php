{{--
 * 掲示板記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- post が firstOrNew で取得しているので、id があるかで空を判断 --}}
@if (empty($post) || empty($post->id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        指定された記事は存在しません。
    </div>
@else
    {{-- 以下、post がある想定の処理 --}}

{{-- 編集、返信ボタンのアクション --}}
<script type="text/javascript">
    function edit_action() {
        form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
        form_bbses_posts{{$frame_id}}.submit();
    }
    function reply_action() {
        form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/plugin/bbses/reply/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
        form_bbses_posts{{$frame_id}}.submit();
    }
</script>

<form method="POST" class="" name="form_bbses_posts{{$frame_id}}">
{{csrf_field()}}
<article>
    <header>
        {{-- タイトル --}}
        <h2>{{$post->title}}</h2>

        {{-- 投稿日時 --}}
        <b>{{$post->created_at->format('Y年n月j日 H時i分')}}</b>
    </header>

    {{-- 記事本文 --}}
    {!! $post->body !!}

    {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
    <footer class="row">
        <div class="col-12 text-right mb-1">
        {{-- 一時保存の表示：自分が編集できる権限の場合 --}}
        @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
            @if ($post->temporary_flag == 1)
                <span class="badge badge-warning align-bottom">一時保存</span>
            @endif
        @endcan

        {{-- 返信ボタンの表示：一時保存でなく、自分が投稿できる権限の場合 --}}
        @can('posts.create',[[null, $frame->plugin_name, $buckets]])
            @if ($post->temporary_flag == 0)
                <div class="custom-control custom-checkbox custom-control-inline mr-0 align-bottom">
                    <input type="checkbox" name="reply" value="1" class="custom-control-input" id="reply{{$frame_id}}">
                    <label class="custom-control-label" for="reply{{$frame_id}}">引用する</label>
                </div>

                <button type="button" class="btn btn-sm btn-primary mr-1" onclick="javascript:reply_action();">
                    <i class="fas fa-comment"></i> <span class="hidden-xs">返信</span>
                </button>
            @endif
        @endcan

        {{-- 編集ボタンの表示：自分が更新できる権限の場合 --}}
        @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
            <button type="button" class="btn btn-sm btn-success" onclick="javascript:edit_action();">
                <i class="far fa-edit"></i> <span class="hidden-xs">編集</span>
            </button>
        @endcan
        </div>
    </footer>
</article>
</form>

{{-- 一覧へ戻る --}}
<nav class="row" aria-label="ページ移動">
    <div class="col-12 text-center my-3">
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</nav>

{{-- スレッドの投稿一覧 --}}
@if ($thread_root_post)
    <span class="badge badge-primary mb-1">スレッドの記事一覧</span>
    <div class="card mb-3">
        <div class="card-header">{{$thread_root_post->title}}</div>
        <div class="card-body">
            {!!$thread_root_post->body!!}
                @foreach ($children_posts as $children_post)
                    <div class="card mt-3">
                        <div class="card-header">{{$children_post->title}}</div>
                        <div class="card-body">
                            {!!$children_post->body!!}
                        </div>
                    </div>
                @endforeach
        </div>
    </div>
@endif

{{-- / post がある想定の処理 --}}
@endif

@endsection
