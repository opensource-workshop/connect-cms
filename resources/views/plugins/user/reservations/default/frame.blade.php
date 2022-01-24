{{--
 * フレーム表示設定編集画面テンプレート。
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.reservations.reservations_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<script type="text/javascript">
    /**
     * 繰り返しradio.change
     */
    $(function () {
        $('input[name="{{ReservationFrameConfig::facility_display_type}}"]').change(function() {
            // 初期表示する施設の表示・非表示
            change_facility_initial_display_type($(this).val());
        });
    });

    /**
     * 初期表示する施設の表示・非表示
     */
    function change_facility_initial_display_type(value) {
        // 繰り返しルールの表示・非表示
        switch (value) {
            case '{{FacilityDisplayType::all}}':
                $('#facility_initial_display_type_id').collapse('hide');
                break;
            case '{{FacilityDisplayType::only}}':
                $('#facility_initial_display_type_id').collapse('show');
        }
    }
</script>

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message_for_frame')

<div class="alert alert-info">
    <i class="fas fa-exclamation-circle"></i> フレームごとの表示設定を変更します。
</div>

<form action="{{url('/')}}/redirect/plugin/reservations/saveView/{{$page->id}}/{{$frame_id}}/$reservation->id#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/editView/{{$page->id}}/{{$frame_id}}/$reservation->bucket_id#frame-{{$frame_id}}">

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">カレンダー初期表示</label>
        <div class="{{$frame->getSettingInputClass()}}">
            @foreach (ReservationCalendarDisplayType::getMembers() as $enum_value => $enum_label)
                <div class="custom-control custom-radio custom-control-inline">
                    @if (FrameConfig::getConfigValueAndOld($frame_configs, ReservationFrameConfig::calendar_initial_display_type, ReservationCalendarDisplayType::month) == $enum_value)
                        <input type="radio" value="{{$enum_value}}" id="calendar_initial_display_type_{{$enum_value}}" name="{{ReservationFrameConfig::calendar_initial_display_type}}" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="{{$enum_value}}" id="calendar_initial_display_type_{{$enum_value}}" name="{{ReservationFrameConfig::calendar_initial_display_type}}" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="calendar_initial_display_type_{{$enum_value}}">{{$enum_label}}</label>
                </div>
            @endforeach
            @include('plugins.common.errors_inline', ['name' => ReservationFrameConfig::calendar_initial_display_type])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}} pt-0">施設</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">

                <div class="row">
                    <div class="col-md">
                        <label>施設表示</label><br>
                        @foreach (FacilityDisplayType::getMembers() as $enum_value => $enum_label)
                            <div class="custom-control custom-radio custom-control-inline">
                                @if (FrameConfig::getConfigValueAndOld($frame_configs, ReservationFrameConfig::facility_display_type, FacilityDisplayType::all) == $enum_value)
                                    <input type="radio" value="{{$enum_value}}" id="facility_display_type_{{$enum_value}}" name="{{ReservationFrameConfig::facility_display_type}}" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="{{$enum_value}}" id="facility_display_type_{{$enum_value}}" name="{{ReservationFrameConfig::facility_display_type}}" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="facility_display_type_{{$enum_value}}">{{$enum_label}}</label>
                            </div>
                        @endforeach
                        @include('plugins.common.errors_inline', ['name' => ReservationFrameConfig::facility_display_type])
                    </div>
                </div>

                <div id="facility_initial_display_type_id" class="row mt-3 collapse">
                    <div class="col-md">
                        <label>初期表示する施設</label><br>
                        <select class="form-control" name="{{ReservationFrameConfig::facility_initial_display_type}}" class="form-control">
                            {{-- <option value=""></option> --}}
                            @foreach ($facilities as $facility)
                                <option value="{{$facility->id}}" @if(FrameConfig::getConfigValueAndOld($frame_configs, ReservationFrameConfig::facility_initial_display_type) == $facility->id) selected="selected" @endif>{{$facility->facility_name}}</option>
                            @endforeach
                        </select>
                        @include('plugins.common.errors_inline', ['name' => ReservationFrameConfig::facility_initial_display_type])
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
            変更確定
        </button>
    </div>
</form>

{{-- 初期状態で開くもの --}}
<script>
    // 初期表示する施設の表示・非表示
    change_facility_initial_display_type('{{FrameConfig::getConfigValueAndOld($frame_configs, ReservationFrameConfig::facility_display_type, FacilityDisplayType::all)}}');
</script>

@endsection
