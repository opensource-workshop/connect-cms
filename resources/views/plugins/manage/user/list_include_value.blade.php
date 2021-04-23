{{--
 * データ表示用テンプレート。
--}}
@php
    $obj = $input_cols->where('users_id', $user->id)->where('users_columns_id', $users_column->id)->first();

    // 複数選択型
    if ($users_column->column_type == UserColumnType::checkbox) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = str_replace('|', ', ', $obj->value);
        }
    }
    // その他の型
    else {
        $value = $obj ? $obj->value : "";
    }

    // 空の場合、なにか出力しないと「項目名<br>値」で出力してるテンプレートは高さがずれてしまうため対応
    if (is_null($value) || $value === '') {
        // change to laravel6.
        // $value = "&nbsp;";
        $value = "\n";
    }
@endphp

{!!nl2br(e($value))!!}
