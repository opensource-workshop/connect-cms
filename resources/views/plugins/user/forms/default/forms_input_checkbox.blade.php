{{--
 * 登録画面(input checkbox)テンプレート。
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
    <div class="container-fluid row">
        @foreach($forms_columns_id_select[$form_obj->id] as $select)

            @php
            // チェック用変数
            $column_checkbox_checked = "";

            // old でチェックされていたもの
            if (!empty(old('forms_columns_value.'.$form_obj->id))) {
                foreach(old('forms_columns_value.'.$form_obj->id) as $old_value) {
                    if ( $old_value == $select['value'] ) {
                        $column_checkbox_checked = " checked";
                    }
                }
            }

            // 画面が戻ってきたもの
            if (isset($request->forms_columns_value) &&
                array_key_exists($form_obj->id, $request->forms_columns_value)) {

                foreach($request->forms_columns_value[$form_obj->id] as $request_value) {
                    if ( $request_value == $select['value'] ) {
                        $column_checkbox_checked = " checked";
                    }
                }
            }
            @endphp

            <div class="custom-control custom-checkbox custom-control-inline">
                <input name="forms_columns_value[{{$form_obj->id}}][]" value="{{$select['value']}}" type="{{$form_obj->column_type}}" class="custom-control-input" id="forms_columns_value[{{$form_obj->id}}]_{{$loop->iteration}}"{{$column_checkbox_checked}}>
                <label class="custom-control-label" for="forms_columns_value[{{$form_obj->id}}]_{{$loop->iteration}}"> {{$select['value']}}</label>
            </div>

{{--
        <div class="col-sm-{{$col_count}}">
            <label class="cc_label_input_group">
                <div class="input-group">
                    <span class="input-group-addon">

                        @php
                        // チェック用変数
                        $column_checkbox_checked = "";

                        // old でチェックされていたもの
                        if (!empty(old('forms_columns_value.'.$form_obj->id))) {
                            foreach(old('forms_columns_value.'.$form_obj->id) as $old_value) {
                                if ( $old_value == $select['value'] ) {
                                    $column_checkbox_checked = " checked";
                                }
                            }
                        }

                        // 画面が戻ってきたもの
                        if (isset($request->forms_columns_value) &&
                            array_key_exists($form_obj->id, $request->forms_columns_value)) {

                            foreach($request->forms_columns_value[$form_obj->id] as $request_value) {
                                if ( $request_value == $select['value'] ) {
                                    $column_checkbox_checked = " checked";
                                }
                            }
                        }
                        @endphp

                        <input name="forms_columns_value[{{$form_obj->id}}][]" value="{{$select['value']}}" type="{{$form_obj->column_type}}"{{$column_checkbox_checked}}>
                    </span>
                    <span class="form-control" style="height: auto;"> {{$select['value']}}</span>
                </div>
            </label>
        </div>
--}}
        @endforeach
        @if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
            <div class="text-danger" style="padding-left: 15px;">
                <span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$form_obj->id")}}
            </div>
        @endif
    </div>
@endif
