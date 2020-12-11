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
    <select id="{{$label_id}}" name="forms_columns_value[{{$form_obj->id}}]" class="custom-select">
        {{-- 選択肢が１つ以外は、空白行表示する。（セレクトボックスで選択肢が1つの場合、選べる選択肢は１つのため、空白行をなくして既に選択状態にする => 単に固定文字を送信させたいだけ） --}}
        @if (count($forms_columns_id_select[$form_obj->id]) !== 1)
            <option value="">選択してください</option>
        @endif

        @foreach($forms_columns_id_select[$form_obj->id] as $select)
            @php
            // セレクトボックス用変数
            $column_selected = "";

            // リクエストした自フレームのみ処理
            if ($frame_id == $request->frame_id) {
                if (old('forms_columns_value.'.$form_obj->id) == $select['value'] ||
                    (isset($request->forms_columns_value) &&
                    array_key_exists($form_obj->id, $request->forms_columns_value) &&
                    $request->forms_columns_value[$form_obj->id] == $select['value'])
                ) {
                    $column_selected = " selected";
                }
            }
            @endphp

            <option value="{{$select['value']}}" {{$column_selected}}>{{$select['value']}}</option>
        @endforeach
    </select>
    @if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
        <div class="d-block text-danger">
            <i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}
        </div>
    @endif
@endif
