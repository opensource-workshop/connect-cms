{{--
 * 新着情報表示画面
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
        <a href="{{url('/')}}/redirect/plugin/whatsnews/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
        @endif
    </p>

<div>
    <dl>
    @foreach($whatsnews as $whatsnew)
        @if ($whatsnews_frame->view_posted_at)
        <dt>
            {{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}
            @if($whatsnew->category)
                <span class="badge cc_category_{{$whatsnew->classname}}">{{$whatsnew->category}}</span>
            @endif
        </dt>
        @endif
        <dd>
            @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}">
                {{$whatsnew->post_title}}
            </a>
            @endif
        </dd>
        @if ($whatsnews_frame->view_posted_name)
        <dd>
            {{$whatsnew->posted_name}}
        </dd>
        @endif
    @endforeach
    </dl>
    {{-- ページング処理 --}}
    {{-- @if ($whatsnews_frame->page_method == 1)
        <div class="text-center">
            {{ $whatsnews->links() }}
        </div>
    @endif --}}
</div>
@endif
@endsection
