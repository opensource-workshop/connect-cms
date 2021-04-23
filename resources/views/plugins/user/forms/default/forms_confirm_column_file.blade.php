{{--
 * 確認画面(confirm file)テンプレート。
--}}
@php
// value 値の取得
if ($uploads && $uploads->where('columns_id', $form_obj->id)) {
    $value_obj = $uploads->where('columns_id', $form_obj->id)->first();
}
@endphp
@if(isset($value_obj)) {{-- ファイルがアップロードされた or もともとアップロードされていて変更がない時 --}}
    <input name="forms_columns_value[{{$form_obj->id}}]" class="form-control" type="hidden" value="{{$value_obj->id}}">
    <a href="{{url('/')}}/file/{{$value_obj->id}}" target="_blank">{{$value_obj->client_original_name}}</a>
@else
    <input name="forms_columns_value[{{$form_obj->id}}]" class="form-control" type="hidden" value="">
@endif
