{{--
 * 施設の予約一覧のメインテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- ダウンロード用フォーム --}}
<form method="post" name="reservation_download" action="{{url('/')}}/manage/reservation/downloadCsv">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_shift_jis() {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で現在の絞り込み条件のユーザをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        reservation_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        reservation_download.submit();
    }
    function submit_download_utf_8() {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で現在の絞り込み条件のユーザをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        reservation_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        reservation_download.submit();
    }
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.reservation.reservation_manage_tab')
    </div>
    <div class="card-body">

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- <div class="row mt-2"> --}}
        <div class="row">
            <div class="col-3 text-left d-flex align-items-end">
                {{-- (左側)件数 --}}
                <span class="badge badge-pill badge-light">{{ $inputs->total() }} 件</span>
            </div>

            <div class="col text-right">
                {{-- (右側)ダウンロードボタン --}}
                <div class="btn-group">
                    <button type="button" class="btn btn-link" onclick="submit_download_shift_jis();">
                        <i class="fas fa-file-download"></i> ダウンロード
                    </button>
                    <button type="button" class="btn btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">ドロップダウンボタン</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="submit_download_shift_jis(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                        <a class="dropdown-item" href="#" onclick="submit_download_utf_8(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 一覧エリア --}}
        <table class="table table-hover cc-font-90">
            <thead>
                <tr class="d-none d-sm-table-row">
                    <th class="d-block d-sm-table-cell">id</th>
                    <th class="d-block d-sm-table-cell text-break">施設名</th>
                    <th class="d-block d-sm-table-cell text-break">利用日From</th>
                    <th class="d-block d-sm-table-cell text-break">利用日To</th>
                    <th class="d-block d-sm-table-cell text-break">登録者</th>
                    <th class="d-block d-sm-table-cell text-break">更新日</th>
                    <th class="d-block d-sm-table-cell text-break">状態</th>
                    <th class="d-block d-sm-table-cell text-break">項目セット値</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inputs as $input)
                    <tr class="@if ($input->hide_flag) table-secondary @endif">
                        <td class="d-table-cell" >
                            <span class="d-sm-none">id：<br /></span>{{$input->id}}
                        </td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">施設名：</span>{{$input->facility_name}}</td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">利用日From：</span>{{$input->start_datetime->format('Y-m-d H:i')}}</td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">利用日To：</span>{{$input->end_datetime->format('Y-m-d H:i')}}</td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">登録者：</span>{{$input->created_name}}</td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">更新日：</span>{{$input->updated_at}}</td>
                        <td class="d-block d-sm-table-cell">
                            @if ($input->status == StatusType::approval_pending)
                                <span class="d-sm-none">状態：</span><span class="badge badge-warning align-bottom">承認待ち</span>
                            @else
                                <span class="d-sm-none">状態：</span>{{StatusType::getDescription($input->status)}}
                            @endif
                        </td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">項目セット値：</span>{{ str_limit(strip_tags($input->inputs_column_value),100,'...') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $inputs->links() }}

    </div>
</div>

@endsection
