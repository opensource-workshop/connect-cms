{{--
 * 検索画面(search checkbox)テンプレート。
--}}
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    @php
        $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
    @endphp
    <div class="container-fluid row">
        @foreach($users_columns_id_select[$user_obj->id] as $select)

            @php
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
                <input name="users_columns_value[{{$user_obj->id}}][]" value="{{$select['value']}}" type="{{$user_obj->column_type}}" class="custom-control-input" id="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}"{{$column_checkbox_checked}}>
                <label class="custom-control-label" for="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}"> {{$select['value']}}</label>
            </div>

        @endforeach
    </div>
@endif
