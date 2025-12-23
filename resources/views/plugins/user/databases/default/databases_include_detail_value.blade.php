{{--
 * データ表示用テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
    $obj = $input_cols->where('databases_columns_id', $column->id)->first();

    // ファイル型
    if ($column->column_type == DatabaseColumnType::file) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = '<a href="' . url('/') . '/file/' . $obj->value . '" target="_blank">' . $obj->client_original_name . '</a>';

            // ダウンロード件数
            if ($column->show_download_button) {
                $value .= '<button class="ml-4 btn btn-sm btn-primary databases-file-download-button" onclick="window.open(\''. url('/') . '/file/' . $obj->value . '\', \'_blank\')">';
                $value .= '<i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span>';
                $value .= '</button>';
            }

            // ダウンロード件数
            if ($column->show_download_count) {
                $value .= '<span class="ml-4 databases-file-download-count-label">ダウンロード数：</span>';
                $value .= '<span class="databases-file-download-count">'. $obj->download_count . '</span>';
            }
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
            $value = '<video src="' . url('/') . '/file/' . $obj->value . '" class="img-fluid" controls></video>';
            if ($column->show_play_count) {
                $value .= '<span class="ml-4 databases-media-play-count-label">再生回数：</span>';
                $value .= '<span class="databases-media-play-count">'. $obj->play_count . '</span>';
            }
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
    // 日付型
    elseif ($column->column_type == DatabaseColumnType::date) {
        if (empty($obj) || empty($obj->value)) {
            $value = '';
        }
        else {
            $value = date('Y/m/d',  strtotime($obj->value));
        }
    }
    // 複数選択型
    elseif ($column->column_type == DatabaseColumnType::checkbox) {
        if (empty($obj)) {
            $value = '';
        }
        else {
            $value = str_replace('|', ', ', $obj->value);
        }
    }
    // 登録日型
    elseif ($column->column_type == DatabaseColumnType::created) {
        // DatabasesPlugin.phpにて、inputでbladeに値を渡すと、値があってもnullになるため、inputsのままでいく
        $value = $inputs->created_at;
    }
    // 更新日型
    elseif ($column->column_type == DatabaseColumnType::updated) {
        $value = $inputs->last_col_updated_at;
    }
    // 表示件数型
    elseif ($column->column_type == DatabaseColumnType::views) {
        $value = $inputs->views;
    }
    // 公開日型
    elseif ($column->column_type == DatabaseColumnType::posted) {
        $value = $inputs->posted_at;
    }
    // 表示順型
    elseif ($column->column_type == DatabaseColumnType::display) {
        $value = $inputs->display_sequence;
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
    {{-- 改行だけして他はエスケープ --}}
    {!!nl2br(e($value))!!}
@endif
