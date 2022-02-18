{{--
 * 掲示板記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上　雅人 <inoue@opensource-workshop.jp>
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

<script type="text/javascript">
    // 編集、返信、承認ボタンのアクション
    function edit_action() {
        form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
        form_bbses_posts{{$frame_id}}.submit();
    }
    function reply_action() {
        form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/plugin/bbses/reply/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
        form_bbses_posts{{$frame_id}}.submit();
    }
    function approval_action() {
        if (!confirm('承認します。\nよろしいですか？')) {
            return false;
        }
        form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/redirect/plugin/bbses/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
        form_bbses_posts{{$frame_id}}.redirect_path.value = "{{url('/')}}/plugin/bbses/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
        form_bbses_posts{{$frame_id}}.submit();
    }
    // ツールチップ有効化
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

<form method="POST" name="form_bbses_posts{{$frame_id}}">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="">

<article>
    <header>
        {{-- タイトル --}}
        <h2>{{$post->title}}</h2>

        {{-- 投稿日時 --}}
        <b>{{$post->created_at->format('Y年n月j日 H時i分')}} [{{$post->created_name}}]</b>
    </header>

    {{-- 記事本文 --}}
    {!! $post->body !!}

    {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
    <footer class="row">
        <div class="col-sm-3 mb-1">
            {{-- いいねボタン --}}
            @include('plugins.common.like', [
                'use_like' => $bbs->use_like,
                'like_button_name' => $bbs->like_button_name,
                'contents_id' => $post->id,
                'like_id' => $post->like_id,
                'like_count' => $post->like_count,
                'like_users_id' => $post->like_users_id,
            ])
        </div>
        <div class="col-sm-9 text-right mb-1">
            {{$post->getUpdatedAt()}}
        </div>
        <div class="col-12 text-right mb-1">
        {{-- 一時保存 --}}
        @if ($post->status == 1)
            <span class="badge badge-warning align-bottom">一時保存</span>
        @endif

        {{-- 承認待ち --}}
        @if ($post->status == 2)
            <span class="badge badge-warning align-bottom">承認待ち</span>
        @endif

        {{-- 返信ボタンの表示：一時保存でなく、自分が投稿できる権限の場合 --}}
        @can('posts.create',[[null, $frame->plugin_name, $buckets]])
            @if ($post->status == 0)
                <div class="custom-control custom-checkbox custom-control-inline mr-0 align-bottom">
                    <input type="checkbox" name="reply" value="1" class="custom-control-input" id="reply{{$frame_id}}">
                    <label class="custom-control-label" for="reply{{$frame_id}}" id="label_reply{{$frame_id}}">引用する</label>
                </div>

                <button type="button" class="btn btn-sm btn-primary mr-1" onclick="javascript:reply_action();">
                    <i class="fas fa-comment"></i> <span class="hidden-xs" id="button_reply{{$frame_id}}">返信</span>
                </button>
            @endif
        @endcan

        {{-- 承認ボタンの表示：自分が承認できる権限の場合 --}}
        @can('posts.approval',[[$post, $frame->plugin_name, $buckets]])
            @if ($post->status == 2)
            <button type="button" class="btn btn-sm btn-primary" onclick="javascript:approval_action();">
                <i class="far fa-edit"></i> <span class="hidden-xs">承認</span>
            </button>
            @endif
        @endcan

        {{-- 編集ボタンの表示：返信の有無確認 --}}
        @if ($post->canEdit())
            {{-- 自分が更新できる権限の場合 --}}
            @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
                <button type="button" class="btn btn-sm btn-success" onclick="javascript:edit_action();">
                    <i class="far fa-edit"></i> <span class="hidden-xs">編集</span>
                </button>
            @endcan
        @endif
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
@include('plugins.user.bbses.default.thread_show')

{{-- / post がある想定の処理 --}}
@endif

@endsection
