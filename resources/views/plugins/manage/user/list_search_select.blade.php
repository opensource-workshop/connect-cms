{{--
 * 検索画面(search select)テンプレート。
--}}
@php
    $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
@endphp
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    <select id="{{$label_id}}" name="users_columns_value[{{$user_obj->id}}]" class="custom-select">
        <option value=""></option>
        @foreach($users_columns_id_select[$user_obj->id] as $select)
            @if ($select['value'] == $value)
                <option value="{{$select['value']}}" selected>{{$select['value']}}</option>
            @else
                <option value="{{$select['value']}}">{{$select['value']}}</option>
            @endif
        @endforeach
    </select>
@endif
