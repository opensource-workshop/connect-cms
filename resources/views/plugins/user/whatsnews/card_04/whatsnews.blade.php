{{--
 * 新着情報表示画面（カード表示）
 *
 * @author 牧野　可也子 <makino@opensource-workshop.jp>
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

@if (isset($whatsnews_frame->rss) && $whatsnews_frame->rss == UseType::use)
<p class="text-left">
    <a href="{{url('/')}}/redirect/plugin/whatsnews/rss/{{$page->id}}/{{$frame_id}}/" title="{{$whatsnews_frame->whatsnew_name}}のRSS2.0"><span class="badge badge-info">RSS2.0</span></a>
</p>
@endif

<div class="container" id="{{ $whatsnews_frame->read_more_use_flag == UseType::use ? 'app_' . $frame->id : '' }}">

    <article class="clearfix">
        <div class="row">
            @foreach($whatsnews as $whatsnew)
            @if (isset($is_template_col_3))
            {{-- カードタイプ３の場合 --}}
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 whatsnew_card mb-2">
            @else
            {{-- カードタイプ４の場合 --}}
            <div class="col-12 col-sm-6 col-md-6 col-lg-3 whatsnew_card mb-2">
            @endif
                @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
                <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}#frame-{{$whatsnew->frame_id}}" style="text-decoration: none; color: initial;">
                @endif
                <div class="p-2 @if (FrameConfig::getConfigValue($frame_configs, WhatsnewFrameConfig::border))border @endif" style="height: 100%;">
                    <dl>
                        {{-- タイトル --}}
                        @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
                            <dt class="text-center whatsnew_title">
                                @if ($whatsnew->post_title)
                                    {{$whatsnew->post_title_strip_tags}}
                                @else
                                    (無題)
                                @endif
                            </dt>
                        @endif

                        {{-- カテゴリ --}}
                        @if( $whatsnew->category )
                            <dd class="text-center whatsnew_category">
                                <div>
                                    <span class="badge cc_category_{{$whatsnew->classname}} mr-2">{{$whatsnew->category}}</span>
                                </div>
                            </dd>
                        @endif

                        {{-- 投稿日 --}}
                        @if ($whatsnews_frame->view_posted_at)
                            <dd class="text-center whatsnew_posted_at">
                                <span class="mr-2">{{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}</span>
                            </dd>
                        @endif

                        {{-- 投稿者 --}}
                        @if( $whatsnews_frame->view_posted_name )
                            <dd class="text-center whatsnew_posted_name">
                                {{$whatsnew->posted_name}}
                            </dd>
                        @endif

                        {{-- サムネイル --}}
                        @if ($whatsnew->first_image_path && FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail))
                            <dd class="text-center whatsnew_thumbnail">
                                @if (empty(FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail_size)))
                                    <img src="{{$whatsnew->first_image_path}}?size=small" class="pb-1" style="max-width: 200px; max-height: 200px;">
                                @else
                                    <img src="{{$whatsnew->first_image_path}}?size=small" class="pb-1" style="max-width: {{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail_size) }}px; max-height: {{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail_size) }}px;">
                                @endif
                            </dd>
                        @endif

                        {{-- 本文 --}}
                        @if (FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail))
                        <dd class="whatsnew_post_detail">
                            @if (FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length) == 0 ||
                                mb_strlen(strip_tags($whatsnew->post_detail)) <= FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length))
                                {{ strip_tags($whatsnew->post_detail) }}
                            @else
                                {{ mb_substr(strip_tags($whatsnew->post_detail), 0, FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length)) }}...
                            @endif
                        </dd>
                        @endif
                    </dl>
                </div>
                @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </article>

    @if ($whatsnews_frame->read_more_use_flag == UseType::use)
        {{-- 「もっと見る」ボタン押下時、非同期で新着一覧をレンダリング --}}
        <article class="clearfix">
            <div class="row">
                <div v-for="whatsnews in whatsnewses" class="col-12 col-sm-6 col-lg-3 whatsnew_card mb-2">
                    <div  class="p-2" style="height: 100%;"
                        v-bind:class="{ 'border': border == show }"
                    >
                    <dl>

                        {{-- タイトル＋リンク --}}
                        <dt v-if="link_pattern[whatsnews.plugin_name] == 'show_page_frame_post'" class="text-center whatsnew_title">
                            <a :href="url + link_base[whatsnews.plugin_name] + '/' + whatsnews.page_id + '/' + whatsnews.frame_id + '/' + whatsnews.post_id + '#frame-' + whatsnews.frame_id">
                                <template v-if="whatsnews.post_title == null || whatsnews.post_title == ''">（無題）</template>
                                <template v-else>@{{ whatsnews.post_title_strip_tags }}</template>
                            </a>
                        </dt>

                        {{-- カテゴリ --}}
                        <dd v-if="whatsnews.category != null && whatsnews.category != ''" class="text-center whatsnew_category">
                            <div>
                                <span :class="'mr-2 badge cc_category_' + whatsnews.classname">@{{ whatsnews.category }}</span>
                            </div>
                        </dd>

                        {{-- 登録日時 --}}
                        <dd v-if="view_posted_at == show" class="text-center whatsnew_posted_at">
                            <span class="mr-2">@{{ moment(whatsnews.posted_at).format('YYYY/MM/DD')}}</span>
                        </dd>

                        {{-- 投稿者 --}}
                        <dd v-if="view_posted_name == show" class="text-center whatsnew_posted_name">
                            @{{ whatsnews.posted_name }}
                        </dd>

                        {{-- サムネイル --}}
                        <dd v-if="thumbnail == show && whatsnews.first_image_path" class="text-center whatsnew_thumbnail">
                            <img v-if="thumbnail_size == 0 || thumbnail_size == ''" v-bind:src="whatsnews.first_image_path" class="pb-1" style="max-width: 200px; max-height: 200px;">
                            <img v-else v-bind:src="whatsnews.first_image_path" class="pb-1" v-bind:style="thumbnail_style">
                        </dd>

                        {{-- 本文 --}}
                        <dd v-if="post_detail == show" class="whatsnew_post_detail">
                            @{{ whatsnews.post_detail_strip_tags }}
                        </dd>

                    </dl>
                </div>
                </div>
            </div>
        </article>
    @endif

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
</div>

    @if ($whatsnews_frame->read_more_use_flag == UseType::use)
        @include('plugins.user.whatsnews.whatsnews_script')
    @endif
@endif
@endsection
