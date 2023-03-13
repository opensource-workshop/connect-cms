{{--
 * 登録画面(所属型)テンプレート。
--}}
{{-- @dd($user_section) --}}

@if ($sections)
    <select id="{{$label_id}}" name="users_columns_value[{{$user_obj->id}}]" class="custom-select @if ($errors->has("users_columns_value.$user_obj->id")) border-danger @endif" @if($user_obj->required) required @endif>
        <option value=""></option>
        @foreach ($sections as $section)
            @if (old('users_columns_value.'.$user_obj->id) == $section['id'] ||
                $section['id'] == $user_section->section_id)
                <option value="{{$section['id']}}" selected>{{$section['name']}}</option>
            @else
                <option value="{{$section['id']}}">{{$section['name']}}</option>
            @endif
        @endforeach
    </select>
    @if ($errors && $errors->has("users_columns_value.$user_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-triangle"></i> {{$errors->first("users_columns_value.$user_obj->id")}}
        </div>
    @endif
@endif
