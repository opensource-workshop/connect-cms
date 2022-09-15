{{--
 * 貸出中一覧画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- ダウンロード用フォーム --}}
<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_shift_jis() {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で貸出累計をダウンロードします。\nよろしいですか？') ) {
            return;
        }
        lent_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        lent_download.submit();
    }
    function submit_download_utf_8() {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で貸出累計をダウンロードします。\nよろしいですか？') ) {
            return;
        }
        lent_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        lent_download.submit();
    }

    /**
     * 貸出期間（From）・貸出期間（To）ボタン押下
     */
    $(function () {
        let calendar_setting = {
            @if (App::getLocale() == ConnectLocale::ja)
                dayViewHeaderFormat: 'YYYY年 M月',
            @endif
            locale: '{{ App::getLocale() }}',
            format: 'YYYY-MM-DD',
            timepicker:false
        };

        // 貸出期間（From）ボタン押下
        $('#lent_term_from').datetimepicker(calendar_setting);
        // 貸出期間（To）ボタン押下
        $('#lent_term_to').datetimepicker(calendar_setting);
    });
</script>

<div class="alert alert-info">
    <i class="fas fa-exclamation-circle"></i> モデレータ用管理画面
</div>

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message_for_frame')

@if (session('lent_error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> {{ session('lent_error') }}
    </div>
@endif

@if ($message)
    <div class="alert alert-primary">
        <i class="fas fa-exclamation-circle"></i> {{$message}}
    </div>
@endif

<div class="card form-group">
    <div class="card-header" id="frame-{{$frame->id}}-requestlist">郵送貸出リクエスト中一覧</div>

    <style type="text/css">
    <!--
    .book-list .table th, .book-list .table td { padding: 0.5em; }
    -->
    </style>

    <div class="book-list table-responsive">
        <table class="table mb-0">
            <thead>
                <tr class="active">
                    <th nowrap>タイトル</th>
                    <th nowrap>バーコード</th>
                    <th nowrap>ユーザーID</th>
                    <th nowrap>リクエスト日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($books_requests as $books_request)
                <tr>
                    <td>{{$books_request->title}}</td>
                    <td>{{$books_request->barcode}}</td>
                    <td>{{$books_request->student_no}}</td>
                    <td>{{$books_request->created_at}}</td>
                </tr>
                <tr>
                    <td colspan="4" style="border-top: 0; padding-top: 0;">
                        <div class="text-center row">
                            <!-- 貸出ボタン -->
                            <div class="col-4 col-sm-3 col-md-2" style="margin-left: auto;">
                                <form action="{{url('/')}}/plugin/opacs/roleLent/{{$page->id}}/{{$frame_id}}/{{$books_request->opacs_books_id}}#frame-{{$frame_id}}" method="GET">
                                    <input type="hidden" name="req_lent_id" value="{{old('req_lent_id', $books_request->id)}}" class="form-control">
                                    <input type="hidden" name="req_student_no" value="{{old('req_student_no', $books_request->student_no)}}" class="form-control">
                                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                                        貸出
                                    </button>
                                </form>
                            </div>

                            <!-- 郵送貸出リクエスト取り消しボタン -->
                            <div class="col-4 col-sm-3 col-md-2">
                                <form action="{{url('/')}}/plugin/opacs/destroyRequest/{{$page->id}}/{{$frame_id}}/{{$books_request->opacs_books_id}}#frame-{{$frame->id}}-lentlist" method="POST">
                                    <input type="hidden" name="req_student_no" value="{{old('req_student_no', $books_request->student_no)}}" class="form-control">
                                    {{csrf_field()}}
                                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('貸出リクエストを取り消します。\nよろしいですか？')"><span class="glyphicon glyphicon-ok"></span><i class="fas fa-trash-alt"></i> 取消</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card form-group">
    <div class="card-header" id="frame-{{$frame->id}}-requestlist">貸出一覧</div>

    <div class="book-list table-responsive">
        <table class="table">
            <thead>
                <tr class="active">
                    <th>貸出日</th>
                    <th>返却予定日</th>
                    <th>タイトル</th>
                    <th>バーコード</th>
                    <th>ログインID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($books_lents as $books_lent)
                <tr>
                    <td>@php echo date('Y-n-j', strtotime($books_lent->updated_at)); @endphp</td>
                    <td>{!!$books_lent->getFormatRreturnScheduled()!!}</td>
                    <td>
                        <a href="{{url('/')}}/plugin/opacs/roleLent/{{$page->id}}/{{$frame_id}}/{{$books_lent->opacs_books_id}}?req_lent_id={{$books_lent->id}}#frame-{{$frame->id}}">
                            {{$books_lent->title}}
                        </a>
                    </td>
                    <td>{{$books_lent->barcode}}</td>
                    <td>{{$books_lent->student_no}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 返却エリア -->
{{--
        <form class="row mb-3" action="{{url('/')}}/plugin/opacs/returnLent/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
            {{ csrf_field() }}
            <!-- 返却用バーコード -->
            <div class="col-12" style="margin-left: auto; text-align: initial;">
                <label class="col-form-label text-md-right" style="float: left;">返却用バーコード <label class="badge badge-danger">必須</label></label>
                <div class="input-group">
                    <input type="text" name="return_barcode" value="{{old('return_barcode')}}" class="form-control" placeholder="バーコードエリア">
                    @if ($errors && $errors->has('return_barcode')) <div class="text-danger">{{$errors->first('return_barcode')}}</div> @endif
                    <small class="text-muted">バーコードリーダーで読み込んでください。</small>
                </div>
            </div>

            <!-- 返却ボタン -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                    返却
                </button>
            </div>
        </form>
--}}
        <form class="row m-3" action="{{url('/')}}/plugin/opacs/returnLent/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
            {{ csrf_field() }}
            <!-- 返却用バーコード -->
            <label class="col-md-4 col-lg-3 text-md-right col-form-label " style="float: left;">返却用バーコード <label class="badge badge-danger">必須</label></label>
            <div class="col-sm-8 col-md-5 col-lg-7">
                <input class="form-control @if ($errors && $errors->has("return_barcode")) border-danger @endif" type="text" name="return_barcode" value="{{old('return_barcode')}}" placeholder="バーコードエリア">
                @include('plugins.common.errors_inline', ['name' => 'return_barcode'])
                <small class=" text-muted">バーコードリーダーで読み込んでください。</small>
            </div>

            <!-- 返却ボタン -->
            <div class="col-sm-4 col-md-3 col-lg-2 text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                    返却
                </button>
            </div>
        </form>

    </div>
</div>

<div class="card form-group">
    <div class="card-header" id="frame-{{$frame->id}}-requestlist">貸出累計</div>
    <div class="card-body">

        <form action="{{url('/')}}/download/plugin/opacs/downloadCsvLent/{{$page->id}}/{{$frame_id}}/{$opac_frame->opacs_id}" method="post" name="lent_download">
            {{ csrf_field() }}
            <input type="hidden" name="character_code" value="">

            <div class="row">

                <div class="col-md-2 col-form-label">期間</div>
                <div class="col-md-10">

                    <div class="form-row">
                        <div class="col-md-4 col-sm-5">
                            <div class="input-group date" id="lent_term_from" data-target-input="nearest">
                                <input type="text" name="lent_term_from" value="{{ old('lent_term_from') }}" class="form-control datetimepicker-input @if ($errors && $errors->has('lent_term_from')) border-danger @endif" data-target="#lent_term_from">
                                <div class="input-group-append" data-target="#lent_term_from" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fas fa-clock"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">~</div>
                        <div class="col-md-4 col-sm-5">
                            <div class="input-group date" id="lent_term_to" data-target-input="nearest">
                                <input type="text" name="lent_term_to" value="{{ old('lent_term_to') }}" class="form-control datetimepicker-input @if ($errors && $errors->has('lent_term_to')) border-danger @endif" data-target="#lent_term_to">
                                <div class="input-group-append" data-target="#lent_term_to" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fas fa-clock"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 pt-1">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-sm" onclick="submit_download_shift_jis();">
                                    <i class="fas fa-file-download"></i> ダウンロード
                                </button>
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="sr-only">ドロップダウンボタン</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" onclick="submit_download_shift_jis(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                    <a class="dropdown-item" href="#" onclick="submit_download_utf_8(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            @include('plugins.common.errors_inline', ['name' => 'lent_term_from'])
                            @include('plugins.common.errors_inline', ['name' => 'lent_term_to'])
                            <small class="text-muted">期間は貸出データの作成日で絞ります。</small>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- 一覧へ戻る -->
<div class="text-center">
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 戻る</button>
</div>

@endsection
