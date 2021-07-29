{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト内検索プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.searchs.searchs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/searchs/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th></th>
                <th>サイト内検索名</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($searchs as $search)
            <tr @if ($searchs_frame->bucket_id == $search->bucket_id) class="cc-active-tr"@endif>
                <td class="d-table-cell">
                    <input type="radio" value="{{$search->bucket_id}}" name="select_bucket"@if ($searchs_frame->bucket_id == $search->bucket_id) checked @endif>
                </td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">サイト内検索名：</span>{{$search->search_name}}</td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$search->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $searchs, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

    <div class="text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示サイト内検索変更</button>
    </div>
</form>
@endsection
