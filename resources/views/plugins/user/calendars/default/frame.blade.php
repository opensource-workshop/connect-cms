{{--
 * フレーム表示設定編集画面テンプレート。
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

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

@if (empty($calendar->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用するカレンダーを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/calendars/saveView/{{$page->id}}/{{$frame_id}}/{{$calendar->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/calendars/editView/{{$page->id}}/{{$frame_id}}/{{$calendar->bucket_id}}#frame-{{$frame_id}}">

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">表示形式</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($calendar_frame->view_format == 0)
                        <input type="radio" value="0" id="view_format_0" name="view_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="view_format_0" name="view_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="view_format_0">フラット形式</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($calendar_frame->view_format == 1)
                        <input type="radio" value="1" id="view_format_1" name="view_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="view_format_1" name="view_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="view_format_1">スレッド形式</label>（※ 準備中）
                </div>
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    変更確定
                </span>
            </button>
        </div>
    </form>
@endif
@endsection
