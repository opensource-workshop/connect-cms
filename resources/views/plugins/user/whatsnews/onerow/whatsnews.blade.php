{{--
 * 新着情報表示画面（１行表示）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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

<div class="container">
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
    
    
    {{-- ページング処理 --}}
    {{-- @if ($whatsnews_frame->page_method == 1)
        <div class="text-center">
            {{ $whatsnews->links() }}
        </div>
    @endif --}}
</div>
@endif
@endsection
