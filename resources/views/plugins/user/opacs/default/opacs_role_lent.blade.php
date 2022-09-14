{{--
 * 書誌貸出（権限あり）テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base')

{{-- メッセージ画面 --}}
@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

@if (session('lent_error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> {{ session('lent_error') }}
    </div>
@endif

{{-- 貸し出し画面 --}}

<table class="table table-bordered cc_responsive_table">
    <thead>
    <tr class="active">
        <th colspan="2">貸し出し書籍情報</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th nowrap>ISBN等</th>
        <td>{{$opacs_books->isbn}}</td>
    </tr>
    <tr>
        <th nowrap>タイトル</th>
        <td>{{$opacs_books->title}}</td>
    </tr>
    <tr>
        <th nowrap>サブタイトル</th>
        <td>{{$opacs_books->subtitle}}</td>
    </tr>
    <tr>
        <th nowrap>シリーズ</th>
        <td>{{$opacs_books->series}}</td>
    </tr>
    <tr>
        <th nowrap>著者</th>
        <td>{{$opacs_books->creator}}</td>
    </tr>
    <tr>
        <th nowrap>出版者</th>
        <td>{{$opacs_books->publisher}}</td>
    </tr>
    <tr>
        <th nowrap>出版年</th>
        <td>{{$opacs_books->publication_year}}</td>
    </tr>
    <tr>
        <th nowrap>頁数</th>
        <td>{{$opacs_books->page_number}}</td>
    </tr>
    <tr>
        <th nowrap>請求記号</th>
        <td>{{$opacs_books->ndc}}</td>
    </tr>
    <tr>
        <th nowrap>配架場所</th>
        <td>{{$opacs_books->shelf}}</td>
    </tr>
    <tr>
        <th nowrap>バーコード</th>
        <td>{{$opacs_books->barcode}}</td>
    </tr>
    <tr @if ($errors->has("not_exist_student_no")) class="table-danger" @endif>
        <th nowrap>郵送貸し出しリクエストユーザー</th>
        <td>
            {{$opacs_books_lents->student_no}}（　{{$user_name}}　）
            @include('plugins.common.errors_inline', ['name' => 'not_exist_student_no'])
        </td>
    </tr>
    <tr>
        <th nowrap>郵送貸し出しリクエスト日</th>
        <td>{{$opacs_books_lents->created_at}}</td>
    </tr>
    <tr>
        <th nowrap>連絡先電話番号</th>
        <td>{{$opacs_books_lents->phone_no}}</td>
    </tr>
    <tr>
        <th nowrap>連絡先メールアドレス</th>
        <td>{{$opacs_books_lents->email}}</td>
    </tr>
    <tr>
        <th nowrap>送付先郵便番号</th>
        <td>{{$opacs_books_lents->postal_code}}</td>
    </tr>
    <tr>
        <th nowrap>送付先住所</th>
        <td>{{$opacs_books_lents->address}}</td>
    </tr>
    <tr>
        <th nowrap>送付先宛て名</th>
        <td>{{$opacs_books_lents->mailing_name}}</td>
    </tr>
    <tr>
        <th nowrap>配送希望</th>
        <td>{{DeliveryRequestFlag::getDescription($opacs_books_lents->delivery_request_flag)}}</td>
    </tr>
    <tr>
        <th nowrap>配送希望日</th>
        <td>{{$opacs_books_lents->delivery_request_date ? $opacs_books_lents->delivery_request_date->format('Y-m-d') : ''}}</td>
    </tr>
    <tr>
        <th nowrap>配送希望時間</th>
        <td>{{$opacs_books_lents->delivery_request_time}}</td>
    </tr>
</table>

<h4><span class="badge badge-primary">貸し出し</span></h4>
<div class="form-group">
    @if ($opacs_books->lend_flag == '9:禁帯出')
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            この書籍は「禁帯出」のため、貸し出しはできません。
        </div>
    @elseif ($done_lent == false )
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            この書籍は既に貸し出し済みです。
        </div>
    @else
        <form action="{{url('/')}}/redirect/plugin/opacs/lent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/opacs/roleLent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}?req_lent_id={{$req_lent_id}}&req_student_no={{$req_student_no}}#frame-{{$frame_id}}">
            <input type="hidden" name="req_lent_id" value="{{old('req_lent_id', $opacs_books_lents->id)}}">
            <input type="hidden" name="req_student_no" value="{{old('req_student_no', $opacs_books_lents->student_no)}}">

            <div class="form-group form-row">
                <div class="col-md-6 col-lg-4">
                    <label class="col-form-label">確認用バーコード <label class="badge badge-danger">必須</label></label>
                    <input class="form-control @if ($errors->has("barcode")) border-danger @endif" type="text" name="barcode" value="{{old('barcode')}}" placeholder="バーコードエリア">
                    @include('plugins.common.errors_inline', ['name' => 'barcode'])
                    <small class="text-muted">バーコードリーダーで読み込んでください。</small>
                </div>

                <!-- 返却日 -->
                <div class="col-md-6 col-lg-4">
                    <label class="col-form-label">返却期限 <label class="badge badge-danger">必須</label></label>
                    <div class="input-group date" id="return_scheduled" data-target-input="nearest">
                        <input type="text" name="return_scheduled" value="{{old('return_scheduled')}}" class="form-control datetimepicker-input @if ($errors->has("return_scheduled")) border-danger @endif" data-target="#return_scheduled"/>
                        <div class="input-group-append" data-target="#return_scheduled" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-clock"></i></div>
                        </div>
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'return_scheduled'])
                    <script type="text/javascript">
                        $(function () {
                            $('#return_scheduled').datetimepicker({
                                locale: 'ja',
                                dayViewHeaderFormat: 'YYYY年 M月',
                                format: 'YYYY/MM/DD'
                            });
                        });
                    </script>
                </div>

                <!-- 学籍番号/教職員番号 -->
                @if (!isset($opacs_books_lents))
                    <div class="col-lg-4">
                        <label class="col-form-label">学籍番号/教職員番号 <label class="badge badge-danger">必須</label></label>
                        <div class="input-group">
                            <input class="form-control @if ($errors->has("req_student_no")) border-danger @endif" type="text" name="req_student_no" value="{{old('req_student_no')}}">
                            @include('plugins.common.errors_inline', ['name' => 'req_student_no'])
                        </div>
                    </div>
                @endif
            </div>

            <!-- 貸し出しボタン -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 貸出</button>
            </div>
        </form>
    @endif
</div>

{{-- 一覧へ戻る --}}
<p class="text-center">
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/lentlist/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
</p>
@endsection
