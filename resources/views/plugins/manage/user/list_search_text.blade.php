{{--
 * 検索画面(search text)テンプレート。
--}}
@php
    $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
    $type = $type ?? $user_obj->column_type;
@endphp
<input name="users_columns_value[{{$user_obj->id}}]" class="form-control" type="{{$type}}" value="{{$value}}" id="{{$label_id}}">
