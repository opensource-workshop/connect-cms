{{--
 * 登録画面(input checkbox)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@if (array_key_exists($database_obj->id, $databases_columns_id_select))
    @php
        // value 値の取得
        $value_obj = (empty($input_cols)) ? null : $input_cols->where('databases_inputs_id', $id)->where('databases_columns_id', $database_obj->id)->first();
        $value = '';
        if (!empty($value_obj)) {
            $value = $value_obj->value;
        }
        // 現在の選択値の決定（リクエスト優先 → old → 既存値分解）
        $selected_values = [];
        if (isset($request->databases_columns_value) &&
            array_key_exists($database_obj->id, $request->databases_columns_value)) {
            $selected_values = (array)$request->databases_columns_value[$database_obj->id];
        } elseif (!is_null(old('databases_columns_value.' . $database_obj->id))) {
            $selected_values = (array)old('databases_columns_value.' . $database_obj->id);
        } elseif (!empty($value)) {
            // 既存値は '|' 区切り
            $selected_values = array_filter($value === '' ? [] : explode('|', $value), function($v){ return $v !== ''; });
        }
    @endphp
    <div class="container-fluid row @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border border-danger @endif">
        @foreach($databases_columns_id_select[$database_obj->id] as $select)
            <div class="custom-control custom-checkbox custom-control-inline">
                <input name="databases_columns_value[{{$database_obj->id}}][]" value="{{$select['value']}}" type="{{$database_obj->column_type}}" class="custom-control-input" id="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}" @if(in_array($select['value'], $selected_values, true)) checked @endif>
                <label class="custom-control-label" for="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}"> {{$select['value']}}</label>
            </div>

        @endforeach
    </div>
    @include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
@endif
