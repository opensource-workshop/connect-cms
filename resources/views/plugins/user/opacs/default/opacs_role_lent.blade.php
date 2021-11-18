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
@if ($errors && $errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        入力内容にエラーがあります。詳しくは各項目を確認してください。
    </div>
@elseif(session('lent_error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('lent_error') }}
    </div>
@endif

@if ($message)
    @if ($message_class)
        <div class="alert alert-{{$message_class}}">
    @else
        <div class="alert alert-primary">
    @endif
        <i class="fas fa-exclamation-circle"></i>
        {{$message}}
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
<tr>
    <th nowrap>郵送貸し出しリクエストユーザー</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->student_no}}　（　{{$user_name}}　）
        @endif
    </td>
</tr>
<tr>
    <th nowrap>郵送貸し出しリクエスト日</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->created_at}}
        @endif
    </td>
</tr>
<tr>
    <th nowrap>連絡先電話番号</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->phone_no}}
        @endif
    </td>
</tr>
<tr>
    <th nowrap>連絡先メールアドレス</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->email}}
        @endif
    </td>
</tr>
<tr>
    <th nowrap>送付先郵便番号</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->postal_code}}
        @endif
    </td>
</tr>
<tr>
    <th nowrap>送付先住所</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->address}}
        @endif
    </td>
</tr>
<tr>
    <th nowrap>送付先宛て名</th>
    <td>
        @if (isset($opacs_books_lents)) 
            {{$opacs_books_lents->mailing_name}}
        @endif
    </td>
</tr>
</table>

@auth
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
            <form action="{{url('/')}}/plugin/opacs/lent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
                {{ csrf_field() }}

                @if (isset($opacs_books_lents))
                    <input type="hidden" name="req_lent_id" value="{{old('req_lent_id', $opacs_books_lents->id)}}" class="form-control">
                    <input type="hidden" name="req_student_no" value="{{old('req_student_no', $opacs_books_lents->student_no)}}" class="form-control">
                @else
                    <input type="hidden" name="req_lent_id" value="" class="form-control">
                @endif

                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="col-12 col-form-label">確認用バーコード <label class="badge badge-danger">必須</label></label>
                        <div class="input-group">
                            <input class="col-12 form-control" type="text" name="barcode" value="{{old('barcode')}}" placeholder="バーコードエリア">
                            @if ($errors && $errors->has('barcode')) <div class="col-12 text-danger">{{$errors->first('barcode')}}</div> @endif
                            <small class="col-12 text-muted">バーコードリーダーで読み込んでください。</small>
                        </div>
                    </div>
                
                    <!-- 返却日 -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="col-form-label">返却期限 <label class="badge badge-danger">必須</label></label>
                        <div class="input-group date" id="return_scheduled" data-target-input="nearest">
                            <input type="text" name="return_scheduled" value="{{old('return_scheduled')}}" class="form-control datetimepicker-input" data-target="#return_scheduled"/>
                            <div class="input-group-append" data-target="#return_scheduled" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-clock"></i></div>
                            </div>
                        </div>
                        @if ($errors && $errors->has('return_scheduled')) <div class="text-danger">{{$errors->first('return_scheduled')}}</div> @endif
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
                    <div class="col-12 col-lg-4">
                        <label class="col-12 col-form-label">学籍番号/教職員番号 <label class="badge badge-danger">必須</label></label>
                        <div class="input-group">
                            <input class="col-12 form-control" type="text" name="req_student_no" value="{{old('req_student_no')}}">
                            @if ($errors && $errors->has('req_student_no')) <div class="col-12 text-danger">{{$errors->first('req_student_no')}}</div> @endif
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- 貸し出しボタン -->
                <div class="row">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 貸出</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endauth

{{-- 一覧へ戻る --}}
<p class="text-center" style="margin-top: 16px;">
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/lentlist/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
</p>
@endsection
