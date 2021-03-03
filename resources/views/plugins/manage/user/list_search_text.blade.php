{{--
 * 検索画面(search text)テンプレート。
--}}
@php
    $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
@endphp
<input name="users_columns_value[{{$user_obj->id}}]" class="form-control" type="{{$user_obj->column_type}}" value="{{$value}}" id="{{$label_id}}">
