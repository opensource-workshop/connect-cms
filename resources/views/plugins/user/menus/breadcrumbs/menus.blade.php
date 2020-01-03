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
@if ($ancestors)
    <nav>
        <ol class="breadcrumb">
            @foreach($ancestors as $ancestor)
                @if ($loop->last)
                    <li class="breadcrumb-item">{{$ancestor->page_name}}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{$ancestor->permanent_link}}">{{$ancestor->page_name}}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
@endsection
