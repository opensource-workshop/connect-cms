{{--
 * 登録画面(radio)テンプレート。
--}}
@php
    if (!isset($value)) {
        // valueがセットされてなければ、value 値の取得
        $value_obj = (empty($input_cols)) ? null : $input_cols->where('users_id', $id)->where('users_columns_id', $user_obj->id)->first();
        $value = '';
        if (!empty($value_obj)) {
            $value = $value_obj->value;
        }
    }
@endphp
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    <div class="container-fluid row">
        @foreach($users_columns_id_select[$user_obj->id] as $select)

            <div class="custom-control custom-radio custom-control-inline">
                @if (old('users_columns_value.'.$user_obj->id) == $select['value'] ||
                     ($select['value'] == $value) ||
                     (isset($request->users_columns_value) &&
                      array_key_exists($user_obj->id, $request->users_columns_value) &&
                      $request->users_columns_value[$user_obj->id] == $select['value'])
                )
                <input type="radio" id="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}" name="users_columns_value[{{$user_obj->id}}]" value="{{$select['value']}}" class="custom-control-input" checked>
            @else
                <input type="radio" id="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}" name="users_columns_value[{{$user_obj->id}}]" value="{{$select['value']}}" class="custom-control-input">
            @endif
                <label class="custom-control-label" for="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}">{{$select['value']}}</label>
            </div>
        @endforeach
    </div>
    @if ($errors && $errors->has("users_columns_value.$user_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-circle"></i> {{$errors->first("users_columns_value.$user_obj->id")}}
        </div>
    @endif
@endif
