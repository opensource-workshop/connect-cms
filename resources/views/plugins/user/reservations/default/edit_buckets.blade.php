{{--
 * 施設予約の設定画面
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

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message')

{{-- メッセージエリア --}}
<div class="alert alert-info">
    @if (empty($reservation) || $create_flag)
        <i class="fas fa-exclamation-circle"></i> 新しい施設予約を登録します。
    @else
        <i class="fas fa-exclamation-circle"></i> 施設予約を変更します。
    @endif
</div>

@if (!$reservation->id && !$create_flag)
@else

@if (empty($reservation->id))
<form action="{{url('/')}}/redirect/plugin/reservations/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/createBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
<form action="{{url('/')}}/redirect/plugin/reservations/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$reservation->id}}#frame-{{$frame->id}}" method="POST">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@endif

    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにreservations_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="reservations_id" value="">
    @else
        <input type="hidden" name="reservations_id" value="{{$reservation->id}}">
    @endif

    {{-- 入力項目エリア --}}
    <div class="form-group">
        {{-- 施設予約名 --}}
        <label class="control-label">施設予約名 <label class="badge badge-danger">必須</span></label></label>
        <input type="text" name="reservation_name" value="{{old('reservation_name', $reservation->reservation_name)}}" class="form-control @if ($errors && $errors->has('reservation_name')) border-danger @endif">
        @include('plugins.common.errors_inline', ['name' => 'reservation_name'])

        {{-- 初期表示設定（月/週） --}}
        <label class="col-form-label">カレンダー初期表示 <label class="badge badge-danger">必須</span></label></label>
        <div class="row">
            <div class="col-md-1">
                <div class="custom-control custom-radio custom-control-inline">
                    {{-- 月 --}}
                    <input type="radio" value="{{ ReservationCalendarDisplayType::month }}" id="calendar_initial_display_type_off" name="calendar_initial_display_type" class="custom-control-input"
                        @if ($reservation->calendar_initial_display_type == ReservationCalendarDisplayType::month || $create_flag)
                            checked="checked"
                        @endif
                     >
                    <label class="custom-control-label" for="calendar_initial_display_type_off">{{ ReservationCalendarDisplayType::getDescription(ReservationCalendarDisplayType::month) }}</label>
                </div>
            </div>
            <div class="col-md-1">
                <div class="custom-control custom-radio custom-control-inline">
                    {{-- 週 --}}
                    <input type="radio" value="{{ ReservationCalendarDisplayType::week }}" id="calendar_initial_display_type_on" name="calendar_initial_display_type" class="custom-control-input"
                        @if ($reservation->calendar_initial_display_type == ReservationCalendarDisplayType::week)
                            checked="checked"
                        @endif
                    >
                    <label class="custom-control-label" for="calendar_initial_display_type_on">{{ ReservationCalendarDisplayType::getDescription(ReservationCalendarDisplayType::week) }}</label>
                </div>
            </div>
        </div>
    </div>

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </a>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                @if (empty($reservation) || $create_flag)
                    登録確定
                @else
                    変更確定
                @endif
                </button>
            </div>

            {{-- 既存施設予約の場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

{{-- 削除ボタン押下時の表示エリア --}}
<div id="collapse{{$frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">施設予約を削除します。<br>よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/reservations/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$reservation->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
