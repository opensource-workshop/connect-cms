{{--
 * 掲示板画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上　雅人 <inoue@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<script type="text/javascript">
    // ツールチップ有効化
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
    @can('frames.edit',[[null, null, null, $frame]])
    {{-- バケツなし --}}
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する掲示板を選択するか、作成してください。</p>
        </div>
    </div>
    @endcan
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'bbses', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endif
@endcan

{{-- リンク表示 --}}
@if (isset($posts))
    @foreach($posts as $post)
        {{-- 一覧での展開方法の判定 --}}
        @if ($plugin_frame->list_format == 2)
            {{-- 一覧での展開方法：すべて閉じる --}}
            <div class="card mb-3">
                {{-- 一覧での展開方法：すべて閉じるの場合は、card のヘッダに根記事のタイトルを表示 --}}
                <div class="card-header">
                    {{$post->title}}
                    @if ($post->status == 1) <span class="badge badge-warning align-bottom">一時保存</span>
                    @elseif ($post->status == 2) <span class="badge badge-warning align-bottom">承認待ち</span>
                    @endif
                </div>
                {{-- 一覧での展開方法：すべて閉じるの場合は、card のボディに根記事を含めた記事のタイトル一覧を表示 --}}
                <div class="card-body">
                    {{-- 根記事（スレッドの記事は古い順なので、根記事は最初） --}}
                    @include('plugins.user.bbses.default.post_title_div', ['view_post' => $post, 'current_post' => null, 'list_class' => 'mb-2'])
                    {{-- スレッド記事 --}}
                    @foreach ($children_posts->where("thread_root_id", $post->id) as $children_post)
                        @include('plugins.user.bbses.default.post_title_div', ['view_post' => $children_post, 'current_post' => null, 'list_class' => 'mb-2'])
                    @endforeach
                </div>
            </div>
        @else
            {{-- 一覧での展開方法：すべて展開 or すべて閉じておく --}}
            <div class="card mb-3">
                <div class="card-header">
                    @include('plugins.user.bbses.default.post_title', ['view_post' => $post, 'current_post' => null, 'list_class' => ''])
                    <span class="float-right">
                        @include('plugins.user.bbses.default.post_created_at_and_name', ['post' => $post])
                    </span>
                </div>
                <div class="card-body">
                    {!!$post->body!!}

                    {{-- いいねボタン --}}
                    @include('plugins.common.like', [
                        'use_like' => $bbs->use_like,
                        'like_button_name' => $bbs->like_button_name,
                        'contents_id' => $post->id,
                        'like_id' => $post->like_id,
                        'like_count' => $post->like_count,
                        'like_users_id' => $post->like_users_id,
                    ])

                    @if ($children_posts->where("thread_root_id", $post->id)->isNotEmpty())
                        {{-- 一覧での展開方法の判定 --}}
                        @if ($plugin_frame->list_format != 0)
                            {{-- 一覧での返信記事の展開方法が閉じる場合 --}}
                            <div class="card mt-3">
                                @if ($plugin_frame->thread_caption)
                                    <div class="card-header">{{$plugin_frame->thread_caption}}</div>
                                @endif
                                <div class="card-body">
                                    @foreach ($children_posts->where("thread_root_id", $post->id) as $children_post)
                                        @include('plugins.user.bbses.default.post_title_div', ['view_post' => $children_post, 'current_post' => null, 'list_class' => 'mb-2'])
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- 一覧での返信記事の展開方法が開く場合 --}}
                            @foreach ($children_posts->where("thread_root_id", $post->id) as $children_post)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        @include('plugins.user.bbses.default.post_title', ['view_post' => $children_post, 'current_post' => null, 'list_class' => ''])
                                        <span class="float-right">
                                            @include('plugins.user.bbses.default.post_created_at_and_name', ['post' => $children_post])
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        {!!$children_post->body!!}

                                        {{-- いいねボタン --}}
                                        @include('plugins.common.like', [
                                            'use_like' => $bbs->use_like,
                                            'like_button_name' => $bbs->like_button_name,
                                            'contents_id' => $children_post->id,
                                            'like_id' => $children_post->like_id,
                                            'like_count' => $children_post->like_count,
                                            'like_users_id' => $children_post->like_users_id,
                                        ])
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        @endif
    @endforeach
@endif

{{-- ページング処理 --}}
@include('plugins.common.user_paginate', ['posts' => $posts, 'frame' => $frame, 'aria_label_name' => $bbs->name])

@endsection
