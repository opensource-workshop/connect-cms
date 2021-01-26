{{--
 * 登録画面(input textarea)テンプレート。
--}}
@php
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('users_id', $id)->where('users_columns_id', $user_obj->id)->first();
    $value = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
    }
@endphp
<textarea name="users_columns_value[{{$user_obj->id}}]" class="form-control" placeholder="{{$user_obj->place_holder}}" id="{{$label_id}}" @if($user_obj->required) required @endif>{{old('users_columns_value.'.$user_obj->id, $value)}}</textarea>
@if ($errors && $errors->has("users_columns_value.$user_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("users_columns_value.$user_obj->id")}}</div>
@endif
