{{--
 * 確認画面(confirm textarea)テンプレート。
--}}
{!!nl2br(e($request->forms_columns_value[$form_obj->id]))!!}
<input name="forms_columns_value[{{$form_obj->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_obj->id]}}">
