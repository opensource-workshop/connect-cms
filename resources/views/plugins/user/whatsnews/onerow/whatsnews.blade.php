{{--
 * 新着情報表示画面（１行表示）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($whatsnews)
<p class="text-left">
    @if (isset($whatsnews_frame->rss) && $whatsnews_frame->rss == 1)
    <a href="{{url('/')}}/redirect/plugin/whatsnews/rss/{{$page->id}}/{{$frame_id}}/" title="{{$whatsnews_frame->whatsnew_name}}のRSS2.0"><span class="badge badge-info">RSS2.0</span></a>
    @endif
</p>

<div class="container" id="{{ $whatsnews_frame->read_more_use_flag == UseType::use ? 'app_' . $frame->id : '' }}">
@foreach($whatsnews as $whatsnew)
    <div class="mb-2 row">
        {{-- 投稿日 --}}
        @if ($whatsnews_frame->view_posted_at)
        <div class="p-0 col-md-2 col-lg text-nowrap" style="display: contents;">
            <span class="mr-2">{{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}</span>
        </div>
        @endif
        
        {{-- カテゴリ --}}
        @if( $whatsnew->category )
        <div class="p-0 col-md-2 col-lg" style="display: contents;">
            <div>
                <span class="badge cc_category_{{$whatsnew->classname}} mr-2">{{$whatsnew->category}}</span>
            </div>
        </div>
        @endif
        
        {{-- タイトル --}}
        <div class="p-0 col-12 col-sm-12 col-md col-lg mr-2 text-truncate">
            @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}#frame-{{$whatsnew->frame_id}}">
                @if ($whatsnew->post_title)
                    {{$whatsnew->post_title}}
                @else
                    (無題)
                @endif
            </a>
            @endif
        </div>
        
        {{-- 投稿者 --}}
        @if( $whatsnews_frame->view_posted_name )
        <div class="p-0 col-12 col-sm-12 col-md-3 col-lg-2 text-right text-nowrap">
            {{$whatsnew->posted_name}}
        </div>
        @endif
    </div>
@endforeach
    @if ($whatsnews_frame->read_more_use_flag == UseType::use)
        {{-- 「もっと見る」ボタン押下時、非同期で新着一覧をレンダリング --}}
        <div v-for="whatsnews in whatsnewses" class="mb-2 row">
            {{-- 登録日時 --}}
            <div v-if="view_posted_at == 1" class="p-0 col-md-2 col-lg text-nowrap" style="display: contents;">
                <span class="mr-2">@{{ moment(whatsnews.posted_at).format('YYYY/MM/DD')}}</span>
            </div>
            {{-- カテゴリ --}}
            <div v-if="whatsnews.category != null && whatsnews.category != ''" class="p-0 col-md-2 col-lg" style="display: contents;">
                <div>
                    <span :class="'mr-2 badge cc_category_' + whatsnews.classname">@{{ whatsnews.category }}</span>
                </div>
            </div>
            {{-- タイトル＋リンク --}}
            <div v-if="link_pattern[whatsnews.plugin_name] == 'show_page_frame_post'" class="p-0 col-12 col-sm-12 col-md col-lg mr-2 text-truncate">
                <a :href="url + link_base[whatsnews.plugin_name] + '/' + whatsnews.page_id + '/' + whatsnews.frame_id + '/' + whatsnews.post_id + '#frame-' + whatsnews.frame_id">
                    <template v-if="whatsnews.post_title == null || whatsnews.post_title == ''">（無題）</template>
                    <template v-else>@{{ whatsnews.post_title }}</template>
                </a>
            </div>
            {{-- 投稿者 --}}
            <div v-if="view_posted_name == 1" class="p-0 col-12 col-sm-12 col-md-3 col-lg-2 text-right text-nowrap">
                @{{ whatsnews.posted_name }}
            </div>
        </div>
    @endif
    
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
        <div v-if="whatsnews_total_count >= offset" class="text-center">
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
