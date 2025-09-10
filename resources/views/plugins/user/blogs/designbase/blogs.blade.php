{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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
        <p class="text-left col-6">
            @if (isset($blog_frame->rss) && $blog_frame->rss == 1)
            <a href="{{url('/')}}/redirect/plugin/blogs/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
            @endif
        </p>
        {{-- 新規登録 --}}
        @can('posts.create',[[null, $frame->plugin_name, $buckets]])
            <p class="text-right col-6">
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
    {{-- 絞り込み機能 --}}
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
<div>
    <dl>
    @forelse($blogs_posts as $post)
        {{-- 投稿日時 --}}
        <dt>
            {{$post->posted_at->format('Y/m/d')}}
            {{-- 投稿者名 --}}
            @if (FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::display)
                [{{$post->created_name}}]
            @endif
            {{-- カテゴリ --}}
            @if ($post->category_view_flag)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
        </dt>

        <dd>
            {{-- タイトル --}}
            <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}"><span class="title">{{$post->post_title}}</span></a>
            {{-- 重要記事設定マーク ※ログイン時のみ表示 --}}
            @if ($post->important == 1 && Auth::user() && (Auth::user()->can('posts.update',[[$post, 'blogs', 'preview_off']]) || $post->created_id == Auth::user()->id))
                <span class="badge badge-pill badge-danger">重要記事に設定</span>
            @endif
        </dd>
    @empty
        <div class="alert alert-info mt-2">記事はありません。</div>
    @endforelse

        {{-- ページング処理 --}}
        @include('plugins.common.user_paginate', ['posts' => $blogs_posts, 'frame' => $frame, 'aria_label_name' => $blog_frame->blog_name])
    </dl>
</div>
@endif
@endsection
