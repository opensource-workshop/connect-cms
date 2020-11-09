{{--
 * 登録画面(input file)テンプレート。
--}}
<input name="forms_columns_value[{{$form_obj->id}}]" type="{{$form_obj->column_type}}">
@if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}</div>
@endif
