{{--
 * 登録画面(input time)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@php
    $value = $request->forms_columns_value[$form_obj->id] ?? null;
@endphp
{{-- 時間 --}}
<div class="input-group date" id="{{ $form_obj->id }}" data-target-input="nearest">
    <input
        type="text"
        name="forms_columns_value[{{ $form_obj->id }}]"
        value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $value)}}@endif"
        class="form-control datetimepicker-input"
        data-target="#{{ $form_obj->id }}"
        id="{{$label_id}}"
    >
    <div class="input-group-append" data-target="#{{ $form_obj->id }}" data-toggle="datetimepicker">
        <div class="input-group-text"><i class="far fa-clock"></i></div>
    </div>
</div>
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])
{{-- DateTimePicker 呼び出し --}}
@include('plugins.common.datetimepicker', ['element_id' => "$form_obj->id", 'format' => 'HH:mm', 'view_mode' => 'clock', 'calendar_icon' => false, 'stepping' => $form_obj->minutes_increments_from])
