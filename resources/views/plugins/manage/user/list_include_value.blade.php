{{--
 * データ表示用テンプレート。
--}}
@php
    $obj = $input_cols->where('users_id', $user->id)->where('users_columns_id', $users_column->id)->first();

    $value = $obj ? $obj->value : "";

    // 空の場合、なにか出力しないと「項目名<br>値」で出力してるテンプレートは高さがずれてしまうため対応
    if (is_null($value) || $value === '') {
        $value = "&nbsp;";
    }
@endphp

{!!nl2br(e($value))!!}
