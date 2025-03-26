{{--
 * 登録画面(input file)テンプレート。
--}}
<input name="forms_columns_value[{{$form_obj->id}}]" type="{{$form_obj->column_type}}" id="{{$label_id}}">
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])
