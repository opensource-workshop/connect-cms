{{--
 * パンくずメニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@include('plugins.user.menus.common.edit_button')
@if ($ancestors_breadcrumbs)
    <nav aria-label="パンくずリスト">
        <ol class="breadcrumb">
        @foreach($ancestors_breadcrumbs as $ancestor)
            {{-- パンくずはdisplay_flag を継承した値を持っていないので、ページの表示フラグを参照 --}}
            @php
                $is_display_ancestor = ($ancestor->base_display_flag == 1);
                if (!$is_display_ancestor && $menu && $menu->select_flag == 1 && $menu->onPage($ancestor->id)) {
                    $is_display_ancestor = true;
                }
            @endphp
            @if ($is_display_ancestor)
                @if ($loop->last)
                    <li class="breadcrumb-item {{$ancestor->getClass()}} active" aria-current="page">{{$ancestor->page_name}}</li>
                @else
                    <li class="breadcrumb-item {{$ancestor->getClass()}}"><a href="{{$ancestor->getUrl()}}" {!!$ancestor->getUrlTargetTag()!!}>{{$ancestor->page_name}}</a></li>
                @endif
            @endif
        @endforeach
        </ol>
    </nav>
@endif
@endsection
