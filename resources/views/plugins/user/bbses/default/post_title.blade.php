{{--
 * 掲示板のタイトルテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@if ($current_post && $current_post->id == $view_post->id)
    {{$view_post->title}}@if ($view_post->status == 1) <span class="badge badge-warning align-bottom">一時保存</span>@elseif ($view_post->status == 2) <span class="badge badge-warning align-bottom">承認待ち</span>@endif
@else
    <a href="{{url('/')}}/plugin/bbses/show/{{$page->id}}/{{$frame_id}}/{{$view_post->id}}#frame-{{$frame_id}}">{{$view_post->title}}</a>@if ($view_post->status == 1) <span class="badge badge-warning align-bottom">一時保存</span>@elseif ($view_post->status == 2) <span class="badge badge-warning align-bottom">承認待ち</span>@endif
@endif
