{{--
 * 確認画面(confirm date)テンプレート。
--}}
{{$request->forms_columns_value[$form_obj->id]}}
<input name="forms_columns_value[{{$form_obj->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_obj->id]}}">
