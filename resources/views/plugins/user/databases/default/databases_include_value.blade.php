{{--
 * データ表示用テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
    $obj = $input_cols->where('databases_inputs_id', $input->id)->where('databases_columns_id', $column->id)->first();

    // ファイル型
    if ($column->column_type == DatabaseColumnType::file) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = '<a href="' . url('/') . '/file/' . $obj->value . '" target="_blank">' . $obj->client_original_name . '</a>';
        }
    }
    // 画像型
    elseif ($column->column_type == DatabaseColumnType::image) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = '<img src="' . url('/') . '/file/' . $obj->value . '" class="img-fluid" />';
        }
    }
    // 動画型
    elseif ($column->column_type == DatabaseColumnType::video) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = '<video src="' . url('/') . '/file/' . $obj->value . '" class="img-fluid" controls />';
        }
    }
    // リンク型
    elseif ($column->column_type == DatabaseColumnType::link) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = '<a href="' . $obj->value . '" target="_blank">' . $obj->value . '</a>';
        }
    }
    // 登録日型
    elseif ($column->column_type == DatabaseColumnType::created) {
        $value = $input->created_at;
    }
    // 更新日型
    elseif ($column->column_type == DatabaseColumnType::updated) {
        $value = $input->updated_at;
    }
    // その他の型
    else {
        $value = $obj ? $obj->value : "";
    }

    // 空の場合、なにか出力しないと「項目名<br>値」で出力してるテンプレートは高さがずれてしまうため対応
    if (is_null($value) || $value === '') {
        $value = "&nbsp;";
    }
@endphp

{{-- ファイル型 --}}
@if ($column->column_type == DatabaseColumnType::file)
    {!!$value!!}
@elseif ($column->column_type == DatabaseColumnType::image)
    {!!$value!!}
@elseif ($column->column_type == DatabaseColumnType::video)
    {!!$value!!}
@elseif ($column->column_type == DatabaseColumnType::link)
    {!!$value!!}
@elseif ($column->column_type == DatabaseColumnType::wysiwyg)
    {!!$value!!}
@else
    {!!nl2br(e($value))!!}
@endif
