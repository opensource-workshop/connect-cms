{{--
検索画面(search 所属型)テンプレート。
--}}
@php
    $value = Session::get('user_search_condition.users_columns_value.' . $user_obj->id);
@endphp
@if ($sections)
    <select id="{{$label_id}}" name="users_columns_value[{{$user_obj->id}}]" class="custom-select">
        <option value=""></option>
        @foreach($sections as $section)
            @if ($section['id'] == $value)
                <option value="{{$section['id']}}" selected>{{$section['name']}}</option>
            @else
                <option value="{{$section['id']}}">{{$section['name']}}</option>
            @endif
        @endforeach
    </select>
@endif
