{{--
 * リンクリスト画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリストプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
    {{-- バケツなし --}}
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">{{ __('messages.empty_bucket', ['plugin_name' => 'リンクリスト']) }}</p>
        </div>
    </div>
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'linklists', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endif
@endcan

{{-- リンク表示 --}}
@if (isset($posts))
    @php
    // view_flag=0時に、カテゴリ表示させずに処理するため、plugin_categories.categories_id を利用（plugin_categories.view_flag=1で外部結合して plugin_categories.categories_idをnull扱いにする）
    $before_plugin_categories_categories_id = null;
    $is_first = true;
    @endphp

    @foreach($posts as $post)

        {{-- 初回 or １つ前と比べてカテゴリIDが変わったら表示 --}}
        @if ($is_first || $before_plugin_categories_categories_id != $post->plugin_categories_categories_id)
            {{-- ２回目以降はdl・ul・ol閉じタグ --}}
            @if (!$is_first)
                @if (!$plugin_frame->type)
                </dl>
                @elseif ($plugin_frame->type == 1 || $plugin_frame->type == 2 || $plugin_frame->type == 3)
                </ul>
                @elseif ($plugin_frame->type == 4 || $plugin_frame->type == 5 || $plugin_frame->type == 6 || $plugin_frame->type == 7 || $plugin_frame->type == 8)
                </ol>
                @endif
            @endif

            {{-- 表示するカテゴリ --}}
            @if ($post->category_view_flag)
                <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
            @endif

            {{-- dl・ul・ol開始タグ --}}
            @if (!$plugin_frame->type)
            <dl>
            @elseif ($plugin_frame->type == 1)
            <ul type="disc">
            @elseif ($plugin_frame->type == 2)
            <ul type="circle">
            @elseif ($plugin_frame->type == 3)
            <ul type="square">
            @elseif ($plugin_frame->type == 4)
            <ol type="1">
            @elseif ($plugin_frame->type == 5)
            <ol type="a">
            @elseif ($plugin_frame->type == 6)
            <ol type="A">
            @elseif ($plugin_frame->type == 7)
            <ol type="i">
            @elseif ($plugin_frame->type == 8)
            <ol type="I">
            @endif

            @php
            $is_first = false;
            @endphp
        @endif

        @if (!$plugin_frame->type)
        {{-- bugfix: dlタグ配下は、dt,ddがそれぞれ1つ以上ないとHTMLバリデーションエラーになるため、dtの空タグを追加 --}}
        <dt></dt>
        <dd>
        @else
        <li>
        @endif

            @can('posts.update',[[null, 'linklists', $buckets]])
                <a href="{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}"><i class="far fa-edit"></a></i>
            @endcan
            @if (empty($post->url))
                {{$post->title}}
            @else
                @if ($post->target_blank_flag)
                    <a href="{{$post->url}}" target="_blank">{{$post->title}}</a>
                @else
                    <a href="{{$post->url}}">{{$post->title}}</a>
                @endif
            @endif
            @if (!empty($post->description))
                <br /><small class="text-muted">{!!nl2br(e($post->description))!!}</small>
            @endif

        @if (!$plugin_frame->type)
        </dd>
        @else
        </li>
        @endif

        @php
        // １つ前のカテゴリID
        $before_plugin_categories_categories_id = $post->plugin_categories_categories_id;
        @endphp
    @endforeach

    {{-- 最後のdl・ul・ol閉じタグ --}}
    @if (!$plugin_frame->type)
    </dl>
    @elseif ($plugin_frame->type == 1 || $plugin_frame->type == 2 || $plugin_frame->type == 3)
    </ul>
    @elseif ($plugin_frame->type == 4 || $plugin_frame->type == 5 || $plugin_frame->type == 6 || $plugin_frame->type == 7 || $plugin_frame->type == 8)
    </ol>
    @endif

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $posts, 'frame' => $frame, 'aria_label_name' => $plugin_frame->name, 'class' => 'mt-3'])

@endif

@endsection
