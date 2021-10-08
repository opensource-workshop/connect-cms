{{--
 * 登録画面(input radio)テンプレート。
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
    <div class="container-fluid row @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border border-danger @endif">
        @foreach($databases_columns_id_select[$database_obj->id] as $select)

            <div class="custom-control custom-radio custom-control-inline">
                @if (old('databases_columns_value.'.$database_obj->id) == $select['value'] ||
                    $select['value'] == $value ||
                    (isset($request->databases_columns_value) &&
                        array_key_exists($database_obj->id, $request->databases_columns_value) &&
                        $request->databases_columns_value[$database_obj->id] == $select['value'])
                )
                <input type="radio" id="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}" name="databases_columns_value[{{$database_obj->id}}]" value="{{$select['value']}}" class="custom-control-input" checked>
            @else
                <input type="radio" id="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}" name="databases_columns_value[{{$database_obj->id}}]" value="{{$select['value']}}" class="custom-control-input">
            @endif
                <label class="custom-control-label" for="databases_columns_value[{{$database_obj->id}}]_{{$loop->iteration}}">{{$select['value']}}</label>
            </div>
        @endforeach
    </div>
    @include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
@endif
