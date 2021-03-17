{{--
 * 検索画面(search agree)テンプレート。
--}}
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    @php
        $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
        $label_value = "以下の内容に同意する。";
    @endphp
    <div class="container-fluid row">
        @php
        // チェック用変数
        $column_checkbox_checked = "";

        if (!empty($value)) {
            // 入力されたデータの中に選択肢が含まれているか否か
            // 選択肢にカンマが含まれている可能性を考慮
            if(strpos($value, $label_value) !== false){
                $column_checkbox_checked = " checked";
            }
        }
        @endphp

        <div class="custom-control custom-checkbox custom-control-inline">
            <input name="users_columns_value[{{$user_obj->id}}][]" value="{{$label_value}}" type="checkbox" class="custom-control-input" id="users_columns_value[{{$user_obj->id}}]"{{$column_checkbox_checked}}>
            <label class="custom-control-label" for="users_columns_value[{{$user_obj->id}}]"> {{$label_value}}</label>
        </div>
    </div>
@endif
