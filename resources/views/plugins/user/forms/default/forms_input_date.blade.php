{{--
 * 登録画面(input date)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@php
    $picker_id = "forms_date_{$frame_id}_{$form_obj->id}";
    $value = $request->forms_columns_value[$form_obj->id] ?? null;
@endphp
{{-- 日付 --}}
<div class="input-group date" id="{{ $picker_id }}" data-target-input="nearest">
    <input
        type="text"
        name="forms_columns_value[{{ $form_obj->id }}]"
        value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $value)}}@endif"
        class="form-control datetimepicker-input"
        data-target="#{{ $picker_id }}"
        placeholder="{{ $form_obj->place_holder }}"
        id="{{$label_id}}"
    >
    <div class="input-group-append" data-target="#{{ $picker_id }}" data-toggle="datetimepicker">
        <div class="input-group-text"><i class="far fa-clock"></i></div>
    </div>
</div>
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])
{{-- DateTimePicker 呼び出し --}}
@include('plugins.common.datetimepicker', ['element_id' => $picker_id, 'format' => 'yyyy/MM/dd', 'clock_icon' => false, 'minimum_date' => $form_obj->getMinimumInputDate()])
