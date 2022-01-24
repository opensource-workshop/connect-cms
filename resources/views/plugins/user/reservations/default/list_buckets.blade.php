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

<form action="{{url('/')}}/redirect/plugin/reservations/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/listBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

    {{-- 登録後メッセージ表示 --}}
    @include('plugins.common.flash_message_for_frame')

    {{-- メッセージエリア --}}
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i> フレームに表示する施設予約を変更します。
    </div>

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
            <thead>
                <tr>
                    <th></th>
                    <th nowrap>施設予約名</th>
                    {{-- <th nowrap>初期表示</th> --}}
                    <th nowrap>施設設定</th>
                    <th>詳細</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $reservation)
                    <tr @if ($reservations_frame->reservations_id == $reservation->id) class="active"@endif>

                        {{-- 選択ラジオ --}}
                        <td class="d-table-cell text-center">
                            <input type="radio" value="{{$reservation->bucket_id}}" name="select_bucket"@if ($reservations_frame->bucket_id == $reservation->bucket_id) checked @endif>
                        </td>

                        <td>
                            <span class="{{$frame->getSettingCaptionClass()}}">施設予約名：</span>{{$reservation->reservation_name}}
                        </td>
                        {{-- <td>
                            <span class="{{$frame->getSettingCaptionClass()}}">初期表示：</span>
                            {{ ReservationCalendarDisplayType::getDescription($reservation->calendar_initial_display_type) }}
                        </td> --}}
                        <td>
                            <span class="{{$frame->getSettingCaptionClass()}}">施設設定：</span>
                            <a href="{{url('/')}}/plugin/reservations/choiceFacilities/{{$page->id}}/{{$frame_id}}/{{ $reservation->id }}#frame-{{$frame->id}}"><i class="far fa-edit"></i></a>
                            {{ $reservation->choice_category_names }}
                        </td>
                        <td nowrap>
                            <span class="{{$frame->getSettingCaptionClass()}}">詳細：</span>
                            <a href="{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame_id}}/{{ $reservation->id }}#frame-{{$frame->id}}" class="btn btn-success btn-sm">
                                <i class="far fa-edit"></i> 設定<span class="{{$frame->getSettingButtonCaptionClass()}}">変更</span>
                            </a>
                        </td>
                        <td nowrap>
                            <span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$reservation->created_at->format('Y/m/d H:i')}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $reservations, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

    {{-- ボタンエリア --}}
    <div class="text-center">
        {{-- キャンセル --}}
        <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        {{-- 変更確定 --}}
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示する施設予約を変更</button>
    </div>
</form>
@endsection
