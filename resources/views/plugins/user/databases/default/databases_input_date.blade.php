{{--
 * 登録画面(input date)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
    $picker_id = "databases_date_{$frame_id}_{$database_obj->id}";
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('databases_inputs_id', $id)->where('databases_columns_id', $database_obj->id)->first();
    $value = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
    }
@endphp
{{-- 日付 --}}
<div class="input-group date" id="{{ $picker_id }}" data-target-input="nearest">
    <input
        type="text"
        name="databases_columns_value[{{ $database_obj->id }}]"
        value="{{old('databases_columns_value.'.$database_obj->id, $value)}}"
        class="form-control datetimepicker-input @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif"
        data-target="#{{ $picker_id }}"
    >
    <div class="input-group-append" data-target="#{{ $picker_id }}" data-toggle="datetimepicker">
        <div class="input-group-text @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif"><i class="far fa-clock"></i></div>
    </div>
</div>
@include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
{{-- DateTimePicker 呼び出し --}}
@include('plugins.common.datetimepicker', ['element_id' => $picker_id, 'format' => 'yyyy/MM/dd', 'clock_icon' => false, 'minimum_date' => $database_obj->getMinimumInputDate()])
