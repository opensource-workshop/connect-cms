{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 権限があれば表示条件表示 --}}
@can('frames.edit',[[null, null, null, $frame]])
    @include('plugins.user.blogs.default.include_scope')
@endcan

@if (isset($buckets) && isset($frame) && $frame->bucket_id)
    <div class="row">
        <p class="text-left col-4">
            @if (isset($blog_frame->rss) && $blog_frame->rss == 1)
                <a href="{{url('/')}}/redirect/plugin/blogs/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
            @endif
        </p>

        {{-- 新規登録 --}}
        @can('posts.create',[[null, $frame->plugin_name, $buckets]])
            <p class="text-right col">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/blogs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        @endcan
    </div>
@else
    {{-- 新規登録 --}}
    @can('frames.edit',[[null, null, null, $frame]])
        <div class="card border-danger">
            <div class="card-body">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するブログを選択するか、作成してください。</p>
            </div>
        </div>
    @endcan
@endif

{{-- ブログ表示 --}}
@if (isset($blogs_posts))
    <div class="float-right ml-2">
        {{-- 表示件数リスト --}}
        @include('plugins.user.blogs.default.include_view_count_spectator')
    </div>
    <div class="float-right ml-2">
        {{-- カテゴリ絞り込み機能 --}}
        @include('plugins.user.blogs.default.include_narrowing_down')
    </div>
    <div class="float-right ml-2">
        {{-- 年月絞り込み機能 --}}
        @include('plugins.user.blogs.default.include_narrowing_down_for_posted_month')
    </div>
    <div class="float-right">
        {{-- 投稿者絞り込み機能 --}}
        @include('plugins.user.blogs.default.include_narrowing_down_for_created_id')
    </div>
    {{-- floatの回り込み解除 --}}
    <div class="clearfix"></div>

    {{-- 絞り込み機能が設定されている場合のみマージンを設定 --}}
    @if ($has_narrowing_down)
        <div class="mt-3"></div>
    @endif

    @if (isset($is_template_titleindex))
    {{-- titleindexテンプレート --}}
    <div class="titleindex">
    @elseif (isset($is_template_sidetitleindex))
    {{-- sidetitleindexテンプレート --}}
    <div class="sidetitleindex">
    @endif

    @forelse($blogs_posts as $post)

        <article class="cc_article">

        @if (isset($is_template_datafirst))
            {{-- datafirstテンプレート --}}
            <header>
                {{-- 投稿日時 --}}
                <b>{{$post->posted_at->format('Y年n月j日 H時i分')}}</b>

                {{-- 投稿者名 --}}
                @if (FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::display)
                    <b>[{{$post->created_name}}]</b>
                @endif

                {{-- タイトル --}}
                <h2><a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">{{$post->post_title}}</a></h2>
            </header>

        @elseif (isset($is_template_titleindex))
            {{-- titleindexテンプレート --}}
            <header>
                {{-- 投稿日時 --}}
                <span class="date">{{$post->posted_at->format('Y年n月j日')}}</span>

                {{-- タイトル --}}
                <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}"><span class="title">{{$post->post_title}}</span></a>

                {{-- 投稿者名 --}}
                @if (FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::display)
                    [{{$post->created_name}}]
                @endif

            </header>

        @elseif (isset($is_template_sidetitleindex))
            {{-- sidetitleindexテンプレート --}}
            @if ($loop->index == 3)
                @break
            @endif

            <header>
                {{-- 投稿日時 --}}
                <span class="date">{{$post->posted_at->format('Y年n月j日')}}</span>

                {{-- タイトル --}}
                <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}"><span class="title">{{$post->post_title}}</span></a>

                {{-- 投稿者名 --}}
                @if (FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::display)
                    [{{$post->created_name}}]
                @endif

            </header>

        @else
            {{-- defaultテンプレート --}}
            <header>
                {{-- タイトル --}}
                <h2><a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">{{$post->post_title}}</a></h2>

                {{-- 投稿日時 --}}
                <b>{{$post->posted_at->format('Y年n月j日 H時i分')}}</b>

                {{-- 投稿者名 --}}
                @if (FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::display)
                    <b>[{{$post->created_name}}]</b>
                @endif

            </header>
        @endif

        {{-- カテゴリ --}}
        @if ($post->category_view_flag)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
        {{-- 重要記事設定マーク ※ログイン時のみ表示 --}}
        @if ($post->important == 1 && Auth::user() && (Auth::user()->can('posts.update',[[$post, 'blogs', 'preview_off']]) || $post->created_id == Auth::user()->id))
            <span class="badge badge-pill badge-danger">重要記事に設定</span>
        @endif

            {{-- titleindexテンプレート・sidetitleindexテンプレートは本文表示しない --}}
            @if (isset($is_template_titleindex) || isset($is_template_sidetitleindex))
            @else
                <div class="clearfix">

                    {{-- 記事本文 --}}
                    {!! $post->post_text !!}

                    {{-- 続きを読む --}}
                    @if ($post->read_more_flag)
                        {{-- 続きを読む & タグありなら、続きを読むとタグの間に余白追加 --}}
                        <div id="post_text2_button_{{$frame->id}}_{{$post->id}}" @isset($post->tags) class="mb-2" @endisset>
                            <button type="button" class="btn btn-light btn-sm border" onclick="$('#post_text2_{{$frame->id}}_{{$post->id}}').show(); $('#post_text2_button_{{$frame->id}}_{{$post->id}}').hide();">
                                <i class="fas fa-angle-down"></i> {{$post->read_more_button}}
                            </button>
                        </div>
                        <div id="post_text2_{{$frame->id}}_{{$post->id}}" style="display: none;" @isset($post->tags) class="mb-2" @endisset>
                            {!! $post->post_text2 !!}
                            <button type="button" class="btn btn-light btn-sm border" onclick="$('#post_text2_button_{{$frame->id}}_{{$post->id}}').show(); $('#post_text2_{{$frame->id}}_{{$post->id}}').hide();">
                                <i class="fas fa-angle-up"></i> {{$post->close_more_button}}
                            </button>
                        </div>
                    @endif

                    {{-- いいねボタン --}}
                    @include('plugins.common.like', [
                        'use_like' => $blog_frame->use_like,
                        'like_button_name' => $blog_frame->like_button_name,
                        'contents_id' => $post->contents_id,
                        'like_id' => $post->like_id,
                        'like_count' => $post->like_count,
                        'like_users_id' => $post->like_users_id,
                    ])

                    {{-- Twitterボタン --}}
                    @include('plugins.common.twitter', [
                        'post_title'        => $post->post_title,
                        'share_connect_url' => "/plugin/blogs/show/$page->id/$frame_id/$post->id",
                        'frame_config_name' => BlogFrameConfig::blog_display_twitter_button,
                    ])

                    {{-- Facebookボタン --}}
                    @include('plugins.common.facebook', [
                        'post_title'        => $post->post_title,
                        'share_connect_url' => "/plugin/blogs/show/$page->id/$frame_id/$post->id",
                        'frame_config_name' => BlogFrameConfig::blog_display_facebook_button,
                    ])

                    {{-- タグ --}}
                    @isset($post->tags)
                        @foreach($post->tags as $tag)
                            <span class="badge badge-secondary">{{$tag}}</span>
                        @endforeach
                    @endisset

                </div>
            @endif

            {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
            <footer class="row">
                <div class="col-12 text-right mb-1">
                @if ($post->status == 2)
                    @can('role_update_or_approval',[[$post, $frame->plugin_name, $buckets]])
                        <span class="badge badge-warning align-bottom">承認待ち</span>
                    @endcan
                    @can('posts.approval',[[$post, $frame->plugin_name, $buckets]])
                        <form action="{{url('/')}}/redirect/plugin/blogs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                            {{ csrf_field() }}
                            <input type="hidden" name="redirect_path" value="{{URL::to($page->permanent_link)}}">
                            <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                <i class="fas fa-check"></i> <span class="d-none d-sm-inline">承認</span>
                            </button>
                        </form>
                    @endcan
                @endif
                @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
                    @if ($post->status == 1)
                        <span class="badge badge-warning align-bottom">一時保存</span>
                    @endif
                    <div class="btn-group">
                        <a href="{{url('/')}}/plugin/blogs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" class="btn btn-success btn-sm">
                            <i class="far fa-edit"></i> <span class="d-none d-sm-inline">編集</span>
                        </a>
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="button_copy{{$post->id}}">
                            <span class="sr-only">ドロップダウンボタン</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{url('/')}}/plugin/blogs/copy/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" class="dropdown-item"><i class="fas fa-copy "></i> コピー</a>
                        </div>
                    </div>
                @endcan
                </div>
            </footer>
        </article>
    @empty
        <div class="alert alert-info mt-2">記事はありません。</div>
    @endforelse

    {{-- sidetitleindexテンプレートはページング表示しない --}}
    @if (isset($is_template_sidetitleindex))
    @else
        {{-- ページング処理 --}}
        @include('plugins.common.user_paginate', ['posts' => $blogs_posts, 'frame' => $frame, 'aria_label_name' => $blog_frame->blog_name, 'class' => 'mt-3'])
    @endif

    {{-- titleindexテンプレート・sidetitleindexテンプレート --}}
    @if (isset($is_template_titleindex) || isset($is_template_sidetitleindex))
    </div>
    @endif

@endif
@endsection
