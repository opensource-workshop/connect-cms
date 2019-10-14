{{--
 * 登録画面(input select)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
@if (array_key_exists($form_obj->id, $forms_columns_id_select))
    @php
        // グループカラムの幅の計算
        $col_count = floor(12/count($forms_columns_id_select[$form_obj->id]));
        if ($col_count < 3) {
            $col_count = 3;
        }
    @endphp
    <select id="forms_columns_value[{{$form_obj->id}}]_{{$loop->iteration}}" name="forms_columns_value[{{$form_obj->id}}]" class="custom-select">
        <option value=""></option>
        @foreach($forms_columns_id_select[$form_obj->id] as $select)

            @if (old('forms_columns_value.'.$form_obj->id) == $select['value'] ||
                 (isset($request->forms_columns_value) &&
                  array_key_exists($form_obj->id, $request->forms_columns_value) &&
                  $request->forms_columns_value[$form_obj->id] == $select['value'])
            )
                <option value="{{$select['value']}}" selected>{{$select['value']}}</option>
            @else
                <option value="{{$select['value']}}">{{$select['value']}}</option>
            @endif
        @endforeach
    </select>
    @if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}
        </div>
    @endif
@endif
