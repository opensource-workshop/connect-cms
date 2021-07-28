{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダープラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.calendars.calendars_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if ($plugin_buckets->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用するカレンダーを選択するか、作成してください。
    </div>
@else
    <form action="{{url('/')}}/redirect/plugin/calendars/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/calendars/listBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
            <thead>
                <tr>
                    <th></th>
                    <th>カレンダー名</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($plugin_buckets as $plugin_bucket)
                <tr @if ($plugin_bucket->bucket_id == $frame->bucket_id) class="cc-active-tr"@endif>
                    <td>
                        <input type="radio" value="{{$plugin_bucket->bucket_id}}" name="select_bucket"@if ($plugin_bucket->bucket_id == $frame->bucket_id) checked @endif>
                        <span class="{{$frame->getSettingCaptionClass()}}">{{$plugin_bucket->name}}</span>
                    </td>
                    <td>{{$plugin_bucket->name}}</td>
                    <td>{{$plugin_bucket->created_at}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- ページング処理 --}}
        @include('plugins.common.user_paginate', ['posts' => $plugin_buckets, 'frame' => $frame, 'aria_label_name' => 'カレンダー選択'])

        <div class="text-center mt-3">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示カレンダー変更</button>
        </div>
    </form>
@endif
@endsection
