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
@endphp
@if (array_key_exists($database_obj->id, $databases_columns_id_select))
    <select id="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}" name="databases_columns_value[{{$database_obj->id}}]" class="custom-select @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif">
        <option value=""></option>
        @foreach($databases_columns_id_select[$database_obj->id] as $select)

            @if (old('databases_columns_value.'.$database_obj->id) == $select['value'] ||
                $select['value'] == $value ||
                (isset($request->databases_columns_value) &&
                    array_key_exists($database_obj->id, $request->databases_columns_value) &&
                    $request->databases_columns_value[$database_obj->id] == $select['value'])
            )
                <option value="{{$select['value']}}" selected>{{$select['value']}}</option>
            @else
                <option value="{{$select['value']}}">{{$select['value']}}</option>
            @endif
        @endforeach
    </select>
    @include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
@endif
