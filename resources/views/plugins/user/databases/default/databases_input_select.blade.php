{{--
 * 登録画面(input select)テンプレート。
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
    // 現在値の決定（リクエスト優先 → old → 既存値）
    $current = null;
    if (isset($request->databases_columns_value) &&
        array_key_exists($database_obj->id, $request->databases_columns_value)) {
        $current = $request->databases_columns_value[$database_obj->id];
    } elseif (!is_null(old('databases_columns_value.' . $database_obj->id))) {
        $current = old('databases_columns_value.' . $database_obj->id);
    } else {
        $current = $value;
    }
@endphp
@if (array_key_exists($database_obj->id, $databases_columns_id_select))
    <select id="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}" name="databases_columns_value[{{$database_obj->id}}]" class="custom-select @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif">
        <option value=""></option>
        @foreach($databases_columns_id_select[$database_obj->id] as $select)

            <option value="{{$select['value']}}" @if($current === $select['value']) selected @endif>{{$select['value']}}</option>
        @endforeach
    </select>
    @include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
@endif
