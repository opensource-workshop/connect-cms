{{--
 * 確認画面(confirm radio)テンプレート。
--}}
@if (array_key_exists($form_obj->id, $request->forms_columns_value))
    <input name="forms_columns_value[{{$form_obj->id}}]" type="hidden" value="{{$request->forms_columns_value[$form_obj->id]}}">{{$request->forms_columns_value[$form_obj->id]}}
@else
    <input name="forms_columns_value[{{$form_obj->id}}]" type="hidden">
@endif
