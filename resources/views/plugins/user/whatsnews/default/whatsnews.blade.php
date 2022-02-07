{{--
 * 新着情報表示画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
@can('frames.edit',[[null, null, null, $frame]])
    {{-- バケツなし --}}
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">{{ __('messages.empty_bucket', ['plugin_name' => '新着情報']) }}</p>
        </div>
    </div>
    @endcan
@endif

@if ($whatsnews)
<p class="text-left">
    @if (isset($whatsnews_frame->rss) && $whatsnews_frame->rss == 1)
    <a href="{{url('/')}}/redirect/plugin/whatsnews/rss/{{$page->id}}/{{$frame_id}}/" title="{{$whatsnews_frame->whatsnew_name}}のRSS2.0"><span class="badge badge-info">RSS2.0</span></a>
    @endif
</p>

<div id="{{ $whatsnews_frame->read_more_use_flag == UseType::use ? 'app_' . $frame->id : '' }}">
    <dl>
    @foreach($whatsnews as $whatsnew)
        {{-- 登録日時、カテゴリ --}}
        @if ($whatsnews_frame->view_posted_at)
        {{-- 表示設定の記事間の罫線を確認（ただし、投稿日を表示しない場合は、次の項目で罫線を表示） --}}
        <dt class="@if (!$loop->first && FrameConfig::getConfigValue($frame_configs, WhatsnewFrameConfig::border))border-top @endif">
            {{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}
            @if($whatsnew->category)
                <span class="badge cc_category_{{$whatsnew->classname}}">{{$whatsnew->category}}</span>
            @endif
        </dt>
        @endif
        {{-- タイトル＋リンク --}}
        {{-- 投稿日を表示しない場合は、ここで表示設定の記事間の罫線を確認 --}}
        <dd class="@if (!$whatsnews_frame->view_posted_at && !$loop->first && FrameConfig::getConfigValue($frame_configs, WhatsnewFrameConfig::border))border-top @endif">
            @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}#frame-{{$whatsnew->frame_id}}">
                @if ($whatsnew->post_title)
                    {{$whatsnew->post_title}}
                @else
                    (無題)
                @endif
            </a>
            @endif
        </dd>

        {{-- 本文 --}}
        @if (FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail))
        <div>
            @if (FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length) == 0 ||
                 mb_strlen(strip_tags($whatsnew->post_detail)) <= FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length))
                {{ strip_tags($whatsnew->post_detail) }}
            @else
                {{ mb_substr(strip_tags($whatsnew->post_detail), 0, FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length)) }}...
            @endif
        </div>
        @endif

        {{-- サムネイル (サムネイル表示がon ＆ 記事中にサムネイルがある場合に表示) --}}
        @if (FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail) && $whatsnew->first_image_path)
        <dd>
            @if (empty(FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail_size)))
                <img src="{{$whatsnew->first_image_path}}?size=small" style="width: 200px;">
            @else
                <img src="{{$whatsnew->first_image_path}}?size=small" style="width:{{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail_size) }}px;">
            @endif
        </dd>
        @endif

        {{-- 投稿者 --}}
        @if ($whatsnews_frame->view_posted_name)
        <dd>
            {{$whatsnew->posted_name}}
        </dd>
        @endif
    @endforeach
    {{-- 「もっと見る」ボタン押下時、非同期で新着一覧をレンダリング ※templateタグはタグとして出力されないタグです。 --}}
    <template v-for="whatsnews in whatsnewses">
        {{-- 登録日時、カテゴリ --}}
        <dt v-if="view_posted_at == 1" v-bind:class="{ 'border-top': border == '1' }">
            @{{ moment(whatsnews.posted_at).format('YYYY/MM/DD') }}
            <span v-if="whatsnews.category != null && whatsnews.category != ''" :class="'badge cc_category_' + whatsnews.classname">@{{ whatsnews.category }}</span>
        </dt>
        {{-- タイトル＋リンク --}}
        <dd v-if="link_pattern[whatsnews.plugin_name] == 'show_page_frame_post'" v-bind:class="{ 'border-top': border == '1' && view_posted_at != 1 }">
            <a :href="url + link_base[whatsnews.plugin_name] + '/' + whatsnews.page_id + '/' + whatsnews.frame_id + '/' + whatsnews.post_id + '#frame-' + whatsnews.frame_id">
                <template v-if="whatsnews.post_title == null || whatsnews.post_title == ''">（無題）</template>
                <template v-else>@{{ whatsnews.post_title }}</template>
            </a>
        </dd>
        {{-- 本文 --}}
        <dd v-if="post_detail == '1'">
            @{{ whatsnews.post_detail_strip_tags }}
        </dd>
        {{-- サムネイル (サムネイル表示がon ＆ 記事中にサムネイルがある場合に表示) --}}
        <dd v-if="post_detail == '1' && whatsnews.first_image_path">
            <img v-if="thumbnail_size == 0 || thumbnail_size == ''" v-bind:src="whatsnews.first_image_path" style="max-width: 200px; max-height: 200px;">
            <img v-else v-bind:src="whatsnews.first_image_path" v-bind:style="thumbnail_style">
        </dd>
        {{-- 投稿者 --}}
        <dd v-if="view_posted_name == 1">
            @{{ whatsnews.posted_name }}
        </dd>
    </template>
    </dl>
    {{-- ページング処理 --}}
    {{-- @if ($whatsnews_frame->page_method == 1)
        <div class="text-center">
            {{ $whatsnews->links() }}
        </div>
    @endif --}}
        {{-- もっと見るボタン ※取得件数が総件数以下で表示 --}}
        @if ($whatsnews_frame->read_more_use_flag == UseType::use)
            @php
                $btn_color = 'btn-';
                $btn_color .= $whatsnews_frame->read_more_btn_transparent_flag == UseType::use ? 'outline-' : '';
                $btn_color .= $whatsnews_frame->read_more_btn_color_type;
            @endphp
            <div v-if="whatsnews_total_count > offset" class="text-center">
                <button class="btn {{ $btn_color }} {{ $whatsnews_frame->read_more_btn_type }}" v-on:click="searchWhatsnewses">
                    {{ $whatsnews_frame->read_more_name }}
                </button>
            </div>
        @endif
        {{-- debug用 --}}
        {{-- limit<input type="text" name="limit" value="" v-model="limit"> --}}
        {{-- offset<input type="text" name="offset" value="" v-model="offset"> --}}
</div>

    @if ($whatsnews_frame->read_more_use_flag == UseType::use)
        @include('plugins.user.whatsnews.whatsnews_script')
    @endif
@endif
@endsection
