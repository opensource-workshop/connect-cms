{{--
 * 登録画面(input time)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@php
    $value_for_time_from = $request->forms_columns_value_for_time_from[$form_obj->id] ?? null;
    $value_for_time_to = $request->forms_columns_value_for_time_to[$form_obj->id] ?? null;
@endphp

<div class="row">

    <div class="col-sm-5">
        {{-- 時間From --}}
        <div class="input-group date" id="{{ $form_obj->id }}_from" data-target-input="nearest">
            <input
                type="text"
                name="forms_columns_value_for_time_from[{{ $form_obj->id }}]"
                value="@if ($frame_id == $request->frame_id){{old('forms_columns_value_for_time_from.'.$form_obj->id, $value_for_time_from)}}@endif"
                class="form-control datetimepicker-input"
                data-target="#{{ $form_obj->id }}_from"
                id="{{$label_id}}"
                title="{{$form_obj->column_name}}の開始時間"
            >
            <div class="input-group-append" data-target="#{{ $form_obj->id }}_from" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="far fa-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center">~</div>
    <div class="col-sm-5">
        {{-- 時間To --}}
        <div class="input-group date" id="{{ $form_obj->id }}_to" data-target-input="nearest">
            <input
                type="text"
                name="forms_columns_value_for_time_to[{{ $form_obj->id }}]"
                value="@if ($frame_id == $request->frame_id){{old('forms_columns_value_for_time_to.'.$form_obj->id, $value_for_time_to)}}@endif"
                class="form-control datetimepicker-input"
                data-target="#{{ $form_obj->id }}_to"
                title="{{$form_obj->column_name}}の終了時間"
            >
            <div class="input-group-append" data-target="#{{ $form_obj->id }}_to" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="far fa-clock"></i></div>
            </div>
        </div>
    </div>

</div>
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])
@include('plugins.common.errors_inline', ['name' => "forms_columns_value_for_time_from.$form_obj->id"])
@include('plugins.common.errors_inline', ['name' => "forms_columns_value_for_time_to.$form_obj->id"])
{{-- DateTimePicker 呼び出し --}}
@include('plugins.common.datetimepicker', ['element_id' => "{$form_obj->id}_from", 'format' => 'HH:mm', 'view_mode' => 'clock', 'calendar_icon' => false, 'stepping' => $form_obj->minutes_increments_from])
@include('plugins.common.datetimepicker', ['element_id' => "{$form_obj->id}_to", 'format' => 'HH:mm', 'view_mode' => 'clock', 'calendar_icon' => false, 'stepping' => $form_obj->minutes_increments_to])
