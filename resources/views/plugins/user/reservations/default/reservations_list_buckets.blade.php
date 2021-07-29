{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.reservations.reservations_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/reservations/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- メッセージエリア --}}
    <div class="alert alert-info mt-2">
        <i class="fas fa-exclamation-circle"></i> フレームに表示するコンテンツを変更します。
    </div>

    <div class="form-group">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead class="thead-light">
            <tr>
                <th class="text-center"></th>
                <th class="text-center">コンテンツ名</th>
                <th class="text-center">カレンダー<br>初期表示</th>
                <th class="text-center">施設名</th>
                <th class="text-center">コンテンツ編集</th>
            </tr>
        </thead>
        <tbody>
        @foreach($reservations as $reservation)
            <tr @if ($reservation_frame->reservations_id == $reservation->id) class="active"@endif>

                {{-- 選択ラジオ --}}
                <td class="text-center">
                    <input type="radio" value="{{$reservation->bucket_id}}" name="select_bucket"@if ($reservation_frame->bucket_id == $reservation->bucket_id) checked @endif>
                </td>
                {{-- 施設予約名 --}}
                <td class="text-center">
                    {{$reservation->reservation_name}}
                </td>
                {{-- 初期表示（月／週） --}}
                <td class="text-center">
                    {{ ReservationCalendarDisplayType::getDescription($reservation->calendar_initial_display_type) }}
                </td>
                <td class="text-center">
                    {{-- 施設名 --}}
                    @if ($reservation->facility_names)
                        {!! nl2br(e($reservation->facility_names)) !!}
                        <br>
                    @endif
                    {{-- 施設登録・変更ボタン --}}
                    <button class="btn btn-success btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/reservations/editFacilities/{{$page->id}}/{{$frame_id}}/{{ $reservation->id }}#frame-{{$frame->id}}'">
                        <i class="far fa-edit"></i> 施設登録・変更
                    </button>
                </td>
                {{-- 設定変更ボタン --}}
                <td class="text-center">
                    <button class="btn btn-success btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame_id}}/{{ $reservation->id }}#frame-{{$frame->id}}'">
                        <i class="far fa-edit"></i> 設定変更
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $reservations, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        {{-- キャンセル --}}
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link) . '#frame-' . $frame->id}}'">
            <i class="fas fa-times"></i> キャンセル
        </button>
        {{-- 変更確定 --}}
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示する施設予約を変更</button>
    </div>
</form>
@endsection
