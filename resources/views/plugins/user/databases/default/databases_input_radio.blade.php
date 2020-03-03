{{--
 * 登録画面(input radio)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@if (array_key_exists($database_obj->id, $databases_columns_id_select))
    @php
        // グループカラムの幅の計算
        $col_count = floor(12/count($databases_columns_id_select[$database_obj->id]));
        if ($col_count < 3) {
            $col_count = 3;
        }
    @endphp
    <div class="container-fluid row">
        @foreach($databases_columns_id_select[$database_obj->id] as $select)

            <div class="custom-control custom-radio custom-control-inline">
                @if (old('databases_columns_value.'.$database_obj->id) == $select['value'] ||
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

{{--
        <div class="col-sm-{{$col_count}}">
            <label class="cc_label_input_group">
                <div class="input-group">
                    <span class="input-group-addon">
                        @if (old('databases_columns_value.'.$database_obj->id) == $select['value'] ||
                                 (isset($request->databases_columns_value) &&
                                  array_key_exists($database_obj->id, $request->databases_columns_value) &&
                                  $request->databases_columns_value[$database_obj->id] == $select['value'])
                            )
                            <input name="databases_columns_value[{{$database_obj->id}}]" value="{{$select['value']}}" type="{{$database_obj->column_type}}" checked>
                        @else
                            <input name="databases_columns_value[{{$database_obj->id}}]" value="{{$select['value']}}" type="{{$database_obj->column_type}}">
                        @endif
                    </span>
                    <span class="form-control" style="height: auto;"> {{$select['value']}}</span>
                </div>
            </label>
        </div>
--}}
        @endforeach
    </div>
    @if ($errors && $errors->has("databases_columns_value.$database_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-circle"></i> {{$errors->first("databases_columns_value.$database_obj->id")}}
        </div>
    @endif
@endif
