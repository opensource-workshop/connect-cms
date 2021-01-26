{{--
 * 登録画面(input select)テンプレート。
--}}
@php
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('users_id', $id)->where('users_columns_id', $user_obj->id)->first();
    $value = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
    }
@endphp
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    <select id="{{$label_id}}" name="users_columns_value[{{$user_obj->id}}]" class="custom-select" @if($user_obj->required) required @endif>
        <option value=""></option>
        @foreach($users_columns_id_select[$user_obj->id] as $select)

            @if (old('users_columns_value.'.$user_obj->id) == $select['value'] ||
                 ($select['value'] == $value) ||
                 (isset($request->users_columns_value) &&
                  array_key_exists($user_obj->id, $request->users_columns_value) &&
                  $request->users_columns_value[$user_obj->id] == $select['value'])
            )
                <option value="{{$select['value']}}" selected>{{$select['value']}}</option>
            @else
                <option value="{{$select['value']}}">{{$select['value']}}</option>
            @endif
        @endforeach
    </select>
    @if ($errors && $errors->has("users_columns_value.$user_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-circle"></i> {{$errors->first("users_columns_value.$user_obj->id")}}
        </div>
    @endif
@endif
