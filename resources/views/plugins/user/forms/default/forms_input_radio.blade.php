{{--
 * 登録画面(input radio)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
@if (array_key_exists($form_obj->id, $forms_columns_id_select))
    @php
        // グループカラムの幅の計算
        $col_count = floor(12/count($forms_columns_id_select[$form_obj->id]));
        if ($col_count < 3) {
            $col_count = 3;
        }
    @endphp
    <div class="container-fluid" style="padding: 0;">
        @foreach($forms_columns_id_select[$form_obj->id] as $select)
        <div class="col-sm-{{$col_count}}">
            <label class="cc_label_input_group">
                <div class="input-group">
                    <span class="input-group-addon">
                        @if (old('forms_columns_value.'.$form_obj->id) == $select['value'] ||
                                 (isset($request->forms_columns_value) &&
                                  array_key_exists($form_obj->id, $request->forms_columns_value) &&
                                  $request->forms_columns_value[$form_obj->id] == $select['value'])
                            )
                            <input name="forms_columns_value[{{$form_obj->id}}]" value="{{$select['value']}}" type="{{$form_obj->column_type}}" checked>
                        @else
                            <input name="forms_columns_value[{{$form_obj->id}}]" value="{{$select['value']}}" type="{{$form_obj->column_type}}">
                        @endif
                    </span>
                    <span class="form-control" style="height: auto;"> {{$select['value']}}</span>
                </div>
            </label>
        </div>
        @endforeach
        @if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
            <div class="text-danger" style="padding-left: 15px;">
                <span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$form_obj->id")}}
            </div>
        @endif
    </div>
@endif
