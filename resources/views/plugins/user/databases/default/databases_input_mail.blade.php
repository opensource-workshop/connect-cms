{{--
 * 登録画面(input mail)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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
<input name="databases_columns_value[{{$database_obj->id}}]" class="form-control @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif" type="text" value="@if ($frame_id == $request->frame_id){{old('databases_columns_value.'.$database_obj->id, $value)}}@endif">
@include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
