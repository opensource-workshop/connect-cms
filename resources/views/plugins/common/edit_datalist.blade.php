{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.' . $frame->plugin_name . '.' . $frame->plugin_name . '_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/change/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}
    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
            <thead>
                <tr>
                    <th></th>
                    <th>{{$frame->plugin_name_full}}名</th>
                    <th>詳細</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plugins as $plugin)
                    <tr @if ($plugin->id == $plugin_frame->id) class="active"@endif>
                        <td class="d-table-cell"><input type="radio" value="{{$plugin->bucket_id}}" name="select_bucket"@if ($plugin_frame->bucket_id == $plugin->bucket_id) checked @endif></td>
                        <td><span class="{{$frame->getSettingCaptionClass()}}">{{$frame->plugin_name_full}}名：</span>{{$plugin->plugin_bucket_name}}</td>
                        <td>
                            <span class="{{$frame->getSettingCaptionClass()}}">詳細：</span>
                            <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/{{$frame->plugin_name}}/editBuckets/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}#frame-{{$frame_id}}">
                                <i class="far fa-edit"></i> 設定変更
                            </a>
                        </td>
                        <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{(new Carbon($plugin->created_at))->format('Y/m/d H:i')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $plugins, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

    <div class="text-center">
        <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示{{$frame->plugin_name_full}}変更</button>
    </div>
</form>

@endsection
