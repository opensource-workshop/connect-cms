{{--
 * データ表示用テンプレート。
--}}
@php
    $obj = $inputs_columns->where('column_id', $column->id)->first();

    // 項目の型で処理を分ける。
    if ($column->column_type == ReservationColumnType::radio) {
        // ラジオ型
        if ($obj) {
            // ラジオボタン項目の場合、valueにはreservations_columns_selectsテーブルのIDが入っているので、該当の選択肢データを取得して選択肢名をセットする
            $filtered_select = $selects->where('column_id', $column->id)->where('id', $obj->value)->first();
            $value = $filtered_select ? $filtered_select->select_name : '';
        } else {
            $value = '';
        }
    } elseif ($column->column_type == ReservationColumnType::created) {
        // 登録日型
        // inputでbladeに値を渡すと、値があってもnullになるため、inputsのままでいく
        $value = $inputs->created_at;
    } elseif ($column->column_type == ReservationColumnType::updated) {
        // 更新日型
        $value = $inputs->updated_at;
    } elseif ($column->column_type == ReservationColumnType::created_name) {
        // 登録者型
        $value = $inputs->created_name;
    } elseif ($column->column_type == ReservationColumnType::updated_name) {
        // 更新者型
        $value = $inputs->updated_name;
    }  else {
        // その他の型
        $value = $obj ? $obj->value : "";
    }

    // 空の場合、なにか出力しないと「項目名<br>値」で出力してるテンプレートは高さがずれてしまうため対応
    if (is_null($value) || $value === '') {
        // change to laravel6.
        // $value = "&nbsp;";
        $value = "\n";
    }
@endphp

{{--
@if ($column->column_type == ReservationColumnType::wysiwyg)
    {!!$value!!}
@else
--}}

{{-- 改行だけして他はエスケープ --}}
{!!nl2br(e($value))!!}

{{--
@endif
--}}
