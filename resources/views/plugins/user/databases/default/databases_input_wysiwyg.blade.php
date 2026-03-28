{{--
 * 登録画面(input wysiwyg)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@php
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('databases_inputs_id', $id)->where('databases_columns_id', $database_obj->id)->first();
    $value = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
    }
@endphp

{{-- WYSIWYG 呼び出し --}}
{{-- bugfix: WYSIWYG項目が複数ある場合に値がPOSTされない不具合の修正。
     target_class をカラム固有にして tinymce.init() のセレクタが1対1で対応するようにする。 --}}
@include('plugins.common.wysiwyg', ['target_class' => 'wysiwyg' . $frame_id . '_' . $database_obj->id])

<div @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) class="border border-danger" @endif>
    {{-- bugfix: target_class に合わせてクラス名をカラム固有にする --}}
    <textarea name="databases_columns_value[{{$database_obj->id}}]" class="form-control wysiwyg{{$frame_id}}_{{$database_obj->id}}">{{old('databases_columns_value.'.$database_obj->id, $value)}}</textarea>
</div>
@include('plugins.common.errors_inline_wysiwyg', ['name' => "databases_columns_value.$database_obj->id"])
