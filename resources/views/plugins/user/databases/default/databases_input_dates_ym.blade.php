{{--
 * 登録画面(input dates_ym)テンプレート。
 * databases_input_text.blade.phpよりコピー
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('databases_inputs_id', $id)->where('databases_columns_id', $database_obj->id)->first();
    $value = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
    }
@endphp
<input name="databases_columns_value[{{$database_obj->id}}]" class="form-control" type="{{$database_obj->column_type}}" value="@if ($frame_id == $request->frame_id){{old('databases_columns_value.'.$database_obj->id, $value)}}@endif">
@if ($errors && $errors->has("databases_columns_value.$database_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("databases_columns_value.$database_obj->id")}}</div>
@endif
