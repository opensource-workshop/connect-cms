{{--
 * 検索画面(search agree)テンプレート。
--}}
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    @php
        $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
    @endphp
    <div class="container-fluid row">
        @php
        // 1番目の選択肢のみ取得
        $select = current($users_columns_id_select[$user_obj->id]);

        // チェック用変数
        $column_checkbox_checked = "";

        if (!empty($value)) {
            // 入力されたデータの中に選択肢が含まれているか否か
            // 選択肢にカンマが含まれている可能性を考慮
            if(strpos($value, $select['value']) !== false){
                $column_checkbox_checked = " checked";
            }
        }
        @endphp

        <div class="custom-control custom-checkbox custom-control-inline">
            <input name="users_columns_value[{{$user_obj->id}}][]" value="{{$select['value']}}" type="checkbox" class="custom-control-input" id="users_columns_value[{{$user_obj->id}}]"{{$column_checkbox_checked}}>
            <label class="custom-control-label" for="users_columns_value[{{$user_obj->id}}]"> {{$select['value']}}</label>
        </div>
    </div>
@endif
