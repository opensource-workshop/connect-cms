{{--
 * データ表示用テンプレート。
--}}
@php
    use App\Models\Common\Uploads;

    $obj = $input_cols->where('forms_inputs_id', $input->id)->where('forms_columns_id', $column->id)->first();

    // ファイル型
    if ($column->column_type == FormColumnType::file) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = '<a href="' . url('/') . '/file/' . $obj->value . '" target="_blank">' . $obj->client_original_name . '</a>';
        }
    }
    // 複数選択型
    elseif ($column->column_type == FormColumnType::checkbox) {
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

{{-- ファイル型 --}}
@if ($column->column_type == FormColumnType::file)
    {!!$value!!}
@else
    {!!nl2br(e($value))!!}
@endif
