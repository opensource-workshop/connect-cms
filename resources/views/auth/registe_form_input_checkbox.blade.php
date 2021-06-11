{{--
 * 登録画面(input checkbox)テンプレート。
--}}
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    @php
        // value 値の取得
        $value_obj = (empty($input_cols)) ? null : $input_cols->where('users_id', $id)->where('users_columns_id', $user_obj->id)->first();
        $value = '';
        if (!empty($value_obj)) {
            $value = $value_obj->value;
        }
    @endphp
    <div class="container-fluid row">
        @foreach($users_columns_id_select[$user_obj->id] as $select)

            @php
            // チェック用変数
            $column_checkbox_checked = "";

            // old でチェックされていたもの
            if (!empty(old('users_columns_value.'.$user_obj->id))) {
                foreach(old('users_columns_value.'.$user_obj->id) as $old_value) {
                    if ( $old_value == $select['value'] ) {
                        $column_checkbox_checked = " checked";
                    }
                }
            }

            // 画面が戻ってきたもの
            if (isset($request->users_columns_value) &&
                array_key_exists($user_obj->id, $request->users_columns_value)) {

                foreach($request->users_columns_value[$user_obj->id] as $request_value) {
                    if ( $request_value == $select['value'] ) {
                        $column_checkbox_checked = " checked";
                    }
                }
            }

            // 変更時のデータベースの値から
            if (!empty($value)) {
                // 入力されたデータの中に選択肢が含まれているか否か
                // 選択肢にカンマが含まれている可能性を考慮
                if(strpos($value,$select['value']) !== false){
                    $column_checkbox_checked = " checked";
                }
            }
            @endphp

            <div class="custom-control custom-checkbox custom-control-inline">
                <input name="users_columns_value[{{$user_obj->id}}][]" value="{{$select['value']}}" type="{{$user_obj->column_type}}" class="custom-control-input" id="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}"{{$column_checkbox_checked}}>
                <label class="custom-control-label" for="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}"> {{$select['value']}}</label>
            </div>

        @endforeach
    </div>
    @if ($errors && $errors->has("users_columns_value.$user_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-circle"></i> {{$errors->first("users_columns_value.$user_obj->id")}}
        </div>
    @endif
@endif
