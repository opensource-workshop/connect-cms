{{--
 * 検索画面(search mail)テンプレート。
--}}
@php
    $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
@endphp
<input name="users_columns_value[{{$user_obj->id}}]" class="form-control" type="text" value="{{$value}}" id="{{$label_id}}">
