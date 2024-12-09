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
    let calendar_setting = {
        @if (App::getLocale() == ConnectLocale::ja)
            dayViewHeaderFormat: 'YYYY年 M月',
        @endif
        locale: '{{ App::getLocale() }}',
        format: 'YYYY-MM-DD HH:mm',
        sideBySide: true
    };

    $(function () {
        // カレンダー時計ボタン押下の設定
        $('#start_datetime').datetimepicker(calendar_setting);
        $('#end_datetime').datetimepicker(calendar_setting);
    });

    /** ダウンロードのsubmit JavaScript */
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
        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')
        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="accordion" id="search_accordion">
            <div class="card">
                <button class="btn btn-link p-0 text-left collapsed" type="button" data-toggle="collapse" data-target="#search_collapse" aria-expanded="false" aria-controls="search_collapse" id="app_reservation_search_condition_button">
                    <div class="card-header" id="app_reservation_search_condition">
                        絞り込み条件 <i class="fas fa-angle-down"></i>@if (Session::has('app_reservation_search_condition'))<span class="badge badge-pill badge-primary ml-2">条件設定中</span>@endif
                   </div>
                </button>
                <div id="search_collapse" class="collapse @if ($errors && count($errors) > 0) show @endif" aria-labelledby="app_reservation_search_condition" data-parent="#search_accordion">
                    <div class="card-body border-bottom">

                        <form name="form_search" id="form_search" class="form-horizontal" method="post" action="{{url('/')}}/manage/reservation/search">
                            {{ csrf_field() }}

                            <!-- 施設名 -->
                            <div class="form-group row">
                                <label for="app_reservation_search_condition_facility_name" class="col-md-3 col-form-label text-md-right">施設名</label>
                                <div class="col-md-9">
                                    <input type="text" name="app_reservation_search_condition[facility_name]" id="app_reservation_search_condition_facility_name" value="{{Session::get('app_reservation_search_condition.facility_name')}}" class="form-control">
                                </div>
                            </div>

                            <!-- 利用日 -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">利用日</label>
                                <div class="col-md-9">

                                    <div class="form-row">
                                        <!-- 利用日From -->
                                        <div class="col-md-6">
                                            <div class="input-group" id="start_datetime" data-target-input="nearest">
                                                @php
                                                    $start_datetime = Session::get("app_reservation_search_condition.start_datetime");
                                                    $start_datetime = $start_datetime ? (new Carbon($start_datetime)) : '';
                                                @endphp
                                                <input type="text" name="app_reservation_search_condition[start_datetime]" value="{{$start_datetime}}" class="form-control datetimepicker-input" data-target="#start_datetime">
                                                <div class="input-group-append" data-target="#start_datetime" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                                </div>
                                                <div class="form-text pl-2">
                                                    ～
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 利用日To -->
                                        <div class="col-md-6">
                                            <div class="input-group" id="end_datetime" data-target-input="nearest">
                                                @php
                                                    $end_datetime = Session::get("app_reservation_search_condition.end_datetime");
                                                    $end_datetime = $end_datetime ? (new Carbon($end_datetime)) : '';
                                                @endphp
                                                <input type="text" name="app_reservation_search_condition[end_datetime]" value="{{$end_datetime}}" class="form-control datetimepicker-input" data-target="#end_datetime">
                                                <div class="input-group-append" data-target="#end_datetime" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @include('plugins.common.errors_inline', ['name' => 'app_reservation_search_condition.start_datetime'])
                                    @include('plugins.common.errors_inline', ['name' => 'app_reservation_search_condition.end_datetime'])

                                </div>
                            </div>

                            <!-- 登録者 -->
                            <div class="form-group row">
                                <label for="app_reservation_search_condition_created_name" class="col-md-3 col-form-label text-md-right">登録者</label>
                                <div class="col-md-9">
                                    <input type="text" name="app_reservation_search_condition[created_name]" id="app_reservation_search_condition_created_name" value="{{Session::get('app_reservation_search_condition.created_name')}}" class="form-control">
                                </div>
                            </div>

                            <!-- 並べ替え -->
                            <div class="form-group row">
                                <label for="sort" class="col-md-3 col-form-label text-md-right">並べ替え</label>
                                <div class="col-md-9">
                                    <select name="app_reservation_search_condition[sort]" id="sort" class="form-control">
                                        <option value="default"@if(Session::get('app_reservation_search_condition.sort') == "default" || !Session::has('app_reservation_search_condition.sort')) selected @endif>施設ID 昇順 & 利用日From 降順</option>
                                        <option value="id_asc"@if(Session::get('app_reservation_search_condition.sort') == "id_asc") selected @endif>ID 昇順</option>
                                        <option value="id_desc"@if(Session::get('app_reservation_search_condition.sort') == "id_desc") selected @endif>ID 降順</option>
                                        <option value="updated_at_asc"@if(Session::get('app_reservation_search_condition.sort') == "updated_at_asc") selected @endif>更新日 昇順</option>
                                        <option value="updated_at_desc"@if(Session::get('app_reservation_search_condition.sort') == "updated_at_desc") selected @endif>更新日 降順</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ボタンエリア -->
                            <div class="form-group text-center pb-4">
                                <div class="row">
                                    <div class="mx-auto">
                                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/reservation/clearSearch')}}'">
                                            <i class="fas fa-times"></i> クリア
                                        </button>
                                        <button type="submit" class="btn btn-primary form-horizontal">
                                            <i class="fas fa-check"></i> 絞り込み
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{-- datetimepicerの小ウィンドウが絞込条件の枠内で隠れてしまうため、余白確保 --}}
                            <div><br></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="row mt-2"> --}}
        <div class="row">
            <div class="col-3 text-left d-flex align-items-end">
                <!-- (左側)件数 -->
                <span class="badge badge-pill badge-light">{{ $inputs->total() }} 件</span>
            </div>

            <div class="col text-right">
                <!-- (右側)ダウンロードボタン -->
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

        <!-- 一覧エリア -->
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
