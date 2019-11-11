{{--
 * 新着情報表示画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}

@if ($whatsnews)
<table>
    @foreach($whatsnews as $whatsnew)
    <tr>
        <td>
            {{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}
        </td>
        <td>
        @if($whatsnew->category)
            <span class="badge cc_category_{{$whatsnew->classname}}">{{$whatsnew->category}}</span>
        @endif
        </td>
        <td>
            @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}">
                {{$whatsnew->post_title}}
            </a>
            @endif
        </td>
    </tr>
    @endforeach
</table>
@endif
