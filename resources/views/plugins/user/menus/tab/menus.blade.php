{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)

<ul class="nav nav-tabs nav-justified d-none d-md-flex">
@foreach($pages as $page)

    {{-- 非表示のページは対象外 --}}
    @if ($page->isView(Auth::user(), false, true, $page_roles))
        @if ($ancestors->contains('id', $page->id))
            <li role="presentation" class="nav-item {{ 'depth-' . $page->depth }} {{$page->getClass()}}"><a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="nav-link active">{{$page->page_name}}</a></li>
        @else
            <li role="presentation" class="nav-item {{ 'depth-' . $page->depth }} {{$page->getClass()}}"><a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="nav-link">{{$page->page_name}}</a></li>
        @endif
    @endif
@endforeach
</ul>

@endif
@endsection
