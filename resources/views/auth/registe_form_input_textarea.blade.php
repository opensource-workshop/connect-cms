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
<textarea name="users_columns_value[{{$user_obj->id}}]" class="form-control @if ($errors->has("users_columns_value.$user_obj->id")) border-danger @endif" placeholder="{{$user_obj->place_holder}}" id="{{$label_id}}" @if($user_obj->required) required @endif>{{old('users_columns_value.'.$user_obj->id, $value)}}</textarea>
@include('plugins.common.errors_inline', ['name' => "users_columns_value.$user_obj->id"])
