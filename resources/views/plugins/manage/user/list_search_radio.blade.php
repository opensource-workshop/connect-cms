{{--
 * 検索画面(search radio)テンプレート。
--}}
@php
    $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
@endphp
@if (array_key_exists($user_obj->id, $users_columns_id_select))
    <div class="container-fluid row">
        @foreach($users_columns_id_select[$user_obj->id] as $select)

            <div class="custom-control custom-radio custom-control-inline">
            @if ($select['value'] == $value)
                <input type="radio" id="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}" name="users_columns_value[{{$user_obj->id}}]" value="{{$select['value']}}" class="custom-control-input" checked>
            @else
                <input type="radio" id="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}" name="users_columns_value[{{$user_obj->id}}]" value="{{$select['value']}}" class="custom-control-input">
            @endif
                <label class="custom-control-label" for="users_columns_value[{{$user_obj->id}}]_{{$loop->iteration}}">{{$select['value']}}</label>
            </div>
        @endforeach
    </div>
@endif
