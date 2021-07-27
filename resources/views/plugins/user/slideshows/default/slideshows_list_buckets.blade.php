{{--
 * スライドショー選択画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.slideshows.slideshows_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/redirect/plugin/slideshows/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    {{-- 選択 --}}
                    <th class="text-nowrap">選択</th>
                    {{-- 更新日 --}}
                    <th class="text-nowrap">
                        <a href="{{url('/')}}/plugin/slideshows/listBuckets/{{$page->id}}/{{$frame_id}}?sort=slideshows_updated_at|{{$order_link["slideshows_updated_at"][0]}}">更新日</a>
                        @if ($request_order_str == "slideshows_updated_at|asc")
                            <i class="fas fa-sort-numeric-down"></i>
                        @elseif ($request_order_str == "slideshows_updated_at|desc")
                            <i class="fas fa-sort-numeric-down-alt"></i>
                        @endif
                    </th>
                    {{-- 使用ページ --}}
                    <th class="text-nowrap">
                        <a href="{{url('/')}}/plugin/slideshows/listBuckets/{{$page->id}}/{{$frame_id}}?sort=page_name|{{$order_link["page_name"][0]}}">使用ページ</a>
                        @if ($request_order_str == "page_name|asc")
                            <i class="fas fa-sort-alpha-down"></i>
                        @elseif ($request_order_str == "page_name|desc")
                            <i class="fas fa-sort-alpha-down-alt"></i>
                        @endif
                    </th>
                    {{-- フレームタイトル --}}
                    <th class="text-nowrap">
                        <a href="{{url('/')}}/plugin/slideshows/listBuckets/{{$page->id}}/{{$frame_id}}?sort=frame_title|{{$order_link["frame_title"][0]}}">フレームタイトル</a>
                        @if ($request_order_str == "frame_title|asc")
                            <i class="fas fa-sort-alpha-down"></i>
                        @elseif ($request_order_str == "frame_title|desc")
                            <i class="fas fa-sort-alpha-down-alt"></i>
                        @endif
                    </th>
                    {{-- スライドショータイトル --}}
                    <th class="text-nowrap">
                        <a href="{{url('/')}}/plugin/slideshows/listBuckets/{{$page->id}}/{{$frame_id}}?sort=slideshows_name|{{$order_link["slideshows_name"][0]}}">スライドショータイトル</a>
                        @if ($request_order_str == "slideshows_name|asc")
                            <i class="fas fa-sort-alpha-down"></i>
                        @elseif ($request_order_str == "slideshows_name|desc")
                            <i class="fas fa-sort-alpha-down-alt"></i>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($buckets_list as $bucket)
                    <tr @if ($bucket->frames_id == $frame_id) class="cc-active-tr"@endif>
                        <td><input type="radio" value="{{$bucket->id}}" name="select_bucket"@if ($bucket->frames_id == $frame_id) checked @endif></td>
                        <td>{{$bucket->slideshows_updated_at}}</td>
                        <td>{{$bucket->page_name}}</td>
                        <td>{{$bucket->frame_title}}</td>
                        <td>{{$bucket->slideshows_name}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $buckets_list, 'frame' => $frame, 'sort' => $request_order_str, 'aria_label_name' => '表示コンテンツ選択'])

    <div class="text-center mt-2">
        <button type="button" class="btn btn-secondary mr-2" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
    </div>
</form>
@endsection
