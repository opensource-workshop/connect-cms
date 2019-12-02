{{--
 * 新着情報表示画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}

@if ($whatsnews)
    <p class="text-left">
        @if (isset($whatsnews_frame->rss) && $whatsnews_frame->rss == 1)
        <a href="{{url('/')}}/redirect/plugin/whatsnews/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
        @endif
    </p>

<div class="d-md-table cc-table-set">
    @foreach($whatsnews as $whatsnew)
    <dl class="d-md-table-row">
        @if ($whatsnews_frame->view_posted_at)
        <dd class="d-md-table-cell">
            {{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}
            @if($whatsnew->category)
                <span class="badge cc_category_{{$whatsnew->classname}}">{{$whatsnew->category}}</span>
            @endif
        </dd>
        @endif
        <dd class="d-md-table-cell">
            @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}">
                {{$whatsnew->post_title}}
            </a>
            @endif
        </dd>
        <dd class="d-md-table-cell">
            @if ($whatsnews_frame->view_posted_name)
                {{$whatsnew->posted_name}}
            @endif
        </dd>
    </dl>
    @endforeach
</div>
@endif
