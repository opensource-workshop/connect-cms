{{--
 * 確認画面(confirm checkbox)テンプレート。
--}}
@if (array_key_exists($form_obj->id, $request->forms_columns_value))
    @foreach($request->forms_columns_value[$form_obj->id] as $checkbox_item)
        <input name="forms_columns_value[{{$form_obj->id}}][]" type="hidden" value="{{$checkbox_item}}">{{$checkbox_item}}@if (!$loop->last), @endif
    @endforeach
@else
    <input name="forms_columns_value[{{$form_obj->id}}][]" type="hidden">
@endif
