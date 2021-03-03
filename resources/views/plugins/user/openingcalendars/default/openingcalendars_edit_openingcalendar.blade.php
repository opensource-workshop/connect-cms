{{--
 * 開館カレンダー編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.openingcalendars.openingcalendars_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$openingcalendar->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する開館カレンダーを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($openingcalendar) || $create_flag)
                新しい開館カレンダー設定を登録します。
            @else
                開館カレンダー設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (!$openingcalendar->id && !$create_flag)
@else
<form action="{{url('/')}}/plugin/openingcalendars/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにopeningcalendars_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="openingcalendars_id" value="">
    @else
        <input type="hidden" name="openingcalendars_id" value="{{$openingcalendar->id}}">
    @endif

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">開館カレンダー名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="openingcalendar_name" value="{{old('openingcalendar_name', $openingcalendar->openingcalendar_name)}}" class="form-control">
            @if ($errors && $errors->has('openingcalendar_name')) <div class="text-danger">{{$errors->first('openingcalendar_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">開館カレンダー名（副題） <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="openingcalendar_sub_name" value="{{old('openingcalendar_sub_name', $openingcalendar->openingcalendar_sub_name)}}" class="form-control">
            @if ($errors && $errors->has('openingcalendar_sub_name')) <div class="text-danger">{{$errors->first('openingcalendar_sub_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">月の表示形式 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <select class="form-control" name="month_format" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('month_format', $openingcalendar->month_format)=="1") selected @endif>1:January / YYYY</option>
                <option value="2" @if(old('month_format', $openingcalendar->month_format)=="2") selected @endif>2:January YYYY</option>
            </select>
            @if ($errors && $errors->has('month_format')) <div class="text-danger">{{$errors->first('month_format')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">週の表示形式 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <select class="form-control" name="week_format" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('week_format', $openingcalendar->week_format)=="1") selected @endif>SUN, MON, ...</option>
                <option value="2" @if(old('week_format', $openingcalendar->week_format)=="2") selected @endif>日, 月, 火, ...</option>
            </select>
            @if ($errors && $errors->has('week_format')) <div class="text-danger">{{$errors->first('week_format')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">過去の表示月数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_before_month" value="{{old('view_before_month', $openingcalendar->view_before_month)}}" class="form-control">
            @if ($errors && $errors->has('view_before_month')) <div class="text-danger">{{$errors->first('view_before_month')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">未来の表示月数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_after_month" value="{{old('view_after_month', $openingcalendar->view_after_month)}}" class="form-control">
            @if ($errors && $errors->has('view_after_month')) <div class="text-danger">{{$errors->first('view_after_month')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">スムーズスクロール</label><br />
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($openingcalendar->smooth_scroll == 0)
                    <input type="radio" value="0" id="smooth_scroll_off" name="smooth_scroll" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="smooth_scroll_off" name="smooth_scroll" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="smooth_scroll_off">しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($openingcalendar->smooth_scroll == 1)
                    <input type="radio" value="1" id="smooth_scroll_on" name="smooth_scroll" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="smooth_scroll_on" name="smooth_scroll" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="smooth_scroll_on">する</label>
            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($openingcalendar) || $create_flag)
                        登録
                    @else
                        変更
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存開館カレンダーの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$openingcalendar_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$openingcalendar_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">開館カレンダーを削除します。<br>この開館カレンダーに記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/openingcalendars/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$openingcalendar->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
