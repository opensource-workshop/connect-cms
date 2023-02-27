{{--
 * 集計結果
 * forms_list_inputs.blade.phpよりコピー
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message')

{{-- ダウンロード用フォーム --}}
<form action="" method="post" name="form_download" class="d-inline">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_shift_jis(id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で登録データをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        form_download.action = "{{url('/')}}/download/plugin/forms/downloadCsvAggregate/{{$page->id}}/{{$frame_id}}/" + id;
        form_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        form_download.submit();
    }
    function submit_download_utf_8(id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で登録データをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        form_download.action = "{{url('/')}}/download/plugin/forms/downloadCsvAggregate/{{$page->id}}/{{$frame_id}}/" + id;
        form_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        form_download.submit();
    }

    $(function () {
        // ツールチップ有効化
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

<div class="row">
    <div class="col-3 text-left d-flex align-items-end">
        {{-- (左側)件数 --}}
        <span class="badge badge-pill badge-light">{{ $inputs->total() }} 件</span>
    </div>

    <div class="col text-right">
        {{-- (右側)ダウンロードボタン --}}
        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-sm" onclick="submit_download_shift_jis({{$form->id}});">
                <i class="fas fa-file-download"></i> ダウンロード
            </button>
            <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">ドロップダウンボタン</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$form->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$form->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
            </div>
        </div>
    </div>
</div>

{{-- データのループ --}}
<table class="table table-bordered table-responsive table-sm mt-2">
    <thead class="thead-light">
        <tr>
            <th nowrap>状態</th>
            @foreach($columns as $column)
                <th>
                    {{mb_substr(strip_tags($column->column_name), 0, 20)}}@if (mb_strlen(strip_tags($column->column_name)) > 20)<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="right" title="{{strip_tags($column->column_name)}}">...</a> @endif
                </th>
            @endforeach
            @if ($form->numbering_use_flag)
                <th nowrap>採番</th>
            @endif
            <th nowrap>登録日時</th>
        </tr>
    </thead>

    <tbody>
    @foreach($inputs as $input)

        @if ($input->status == FormStatusType::temporary)
        {{-- 仮登録 --}}
        <tr class="table-warning">
        @elseif ($input->status == FormStatusType::delete)
        {{-- 削除 --}}
        <tr class="table-danger">
        @else
        {{-- 本登録 --}}
        <tr>
        @endif

            <td nowrap>{{$input->status}}</td>

            @foreach($columns as $column)
                <td style="min-width: 100px;">
                    @include('plugins.user.forms.default.forms_include_value')
                </td>
            @endforeach

            @if ($form->numbering_use_flag)
                <td>
                    {{$input->number_with_prefix}}
                </td>
            @endif

            <td nowrap>
                {{$input->created_at}}
            </td>

        </tr>
    @endforeach
    </tbody>
</table>

<table class="table-bordered table-sm">
    <tbody>
    <tr>
        <td>状態:0 = 本登録</td>
        <td class="table-warning">状態:1 = 仮登録</td>
        <td class="table-danger">状態:9 = 削除</td>
    </tr>
    </tbody>
</table>

{{-- ページング処理 --}}
@include('plugins.common.user_paginate', ['posts' => $inputs, 'frame' => $frame, 'aria_label_name' => $form->forms_name, 'class' => 'form-group mt-3'])

{{-- ボタン --}}
<div class="text-center pt-2">
    <div class="row">
        <div class="col">
            <a href="{{url($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary">
                <i class="fas fa-chevron-left"></i> フォームへ
            </a>
        </div>
    </div>
</div>

@endsection
