{{--
 * 書誌データ詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@if ($errors && $errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        入力内容にエラーがあります。詳しくは各項目を確認してください。
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

<table class="table table-bordered cc_responsive_table">
<thead>
<tr class="active">
    <th colspan="2">書籍情報</th>
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
    <th>状況</th>
    @if ($opacs_books->lent_flag == 1)
        <td>
            <span style="color: red;"><i class="fas fa-user"></i></span> 
            貸し出し中（返却予定日：@php echo date('Y年n月j日', strtotime($opacs_books->return_scheduled)); @endphp）
            </span>
        </td>
    @elseif ($opacs_books->lent_flag == 2)
        <td>
            <span style="color: red;"><i class="fas fa-user"></i></span> 
            貸し出しリクエスト中（返却予定日：@php echo date('Y年n月j日', strtotime($opacs_books->return_scheduled)); @endphp）
            </span>
        </td>
    @endif
</tr>
</table>

@auth
    <h4><span class="badge badge-primary">貸し出し</span></h4>

    @if ($opacs_books->lend_flag == '9:禁帯出') 
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            この書籍は「禁帯出」のため、貸し出しはできません。
        </div>
    @elseif ($opacs_books->lent_flag == 1) 
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            この書籍は現在、貸し出し中のため、貸し出しはできません。
        </div>
    @elseif ($opacs_books->lent_flag == 2) 
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            この書籍は現在、貸し出しリクエスト中のため、貸し出しはできません。
        </div>
    @else
        <div class="form-group">
            <form action="/plugin/opacs/lent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-4">
                        <label class="control-label">学籍番号</label><label class="badge badge-danger">必須</label>
                        <input type="text" name="student_no" value="{{old('student_no')}}" class="form-control">
                        @if ($errors && $errors->has('student_no')) <div class="text-danger">{{$errors->first('student_no')}}</div> @endif
                    </div>
                    <div class="col-sm-4">
                        <label class="control-label">返却予定日</label><label class="badge badge-danger">必須</label>

                        <div class="input-group date" id="return_scheduled" data-target-input="nearest">
                            <input type="text" name="return_scheduled" value="{{old('return_scheduled')}}" class="form-control datetimepicker-input" data-target="#return_scheduled"/>
                            <div class="input-group-append" data-target="#return_scheduled" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
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
                    <div class="col-sm-3">
                        <label class="control-label">借りる</label>
                        <button type="button" class="btn btn-primary form-control" onclick="javascript:form_lent.submit();"><i class="fas fa-check"></i> 借りました。</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <h4><span class="badge badge-primary">郵送貸し出しリクエスト</span></h4>

    <div class="form-group">

        @if ($opacs_books->lend_flag == '9:禁帯出') 
            <div class="alert alert-warning" style="margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は「禁帯出」のため、貸し出しはできません。
            </div>
        @elseif ($opacs_books->lent_flag == 1) 
            <div class="alert alert-warning" style="margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は現在、貸し出し中のため、郵送貸し出しリクエストはできません。
            </div>
        @elseif ($opacs_books->lent_flag == 2) 
            <div class="alert alert-warning" style="margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は現在、貸し出しリクエスト中のため、郵送貸し出しリクエストはできません。
            </div>
        @else
            <form action="/plugin/opacs/requestLent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" id="form_requestLent" name="form_requestLent" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-4" style="margin-top: 8px;">
                        <label class="control-label">学籍番号</label><label class="badge badge-danger">必須</label>
                        <input type="text" name="req_student_no" value="{{old('req_student_no')}}" class="form-control">
                        @if ($errors && $errors->has('req_student_no')) <div class="text-danger">{{$errors->first('req_student_no')}}</div> @endif
                    </div>


                    <div class="col-sm-4" style="margin-top: 8px;">
                        <label class="control-label">返却予定日</label><label class="badge badge-danger">必須</label>

                        <div class="input-group date" id="req_return_scheduled" data-target-input="nearest">
                            <input type="text" name="req_return_scheduled" value="{{old('req_return_scheduled')}}" class="form-control datetimepicker-input" data-target="#req_return_scheduled"/>
                            <div class="input-group-append" data-target="#req_return_scheduled" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        @if ($errors && $errors->has('req_return_scheduled')) <div class="text-danger">{{$errors->first('req_return_scheduled')}}</div> @endif
                        <script type="text/javascript">
                            $(function () {
                                $('#req_return_scheduled').datetimepicker({
                                    locale: 'ja',
                                    dayViewHeaderFormat: 'YYYY年 M月',
                                    format: 'YYYY/MM/DD'
                                });
                            });
                        </script>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="control-label" style="margin-top: 8px;">連絡先電話番号</label>
                        <input type="text" name="req_phone_no" value="{{old('req_phone_no')}}" class="form-control">
                    </div>
                    <div class="col-sm-4">
                        <label class="control-label" style="margin-top: 8px;">連絡先メールアドレス</label>
                        <input type="text" name="req_email" value="{{old('req_email')}}" class="form-control">
                    </div>
                    <div class="col-sm-3">
                        <label class="control-label" style="margin-top: 8px;">リクエスト</label>
                        <button type="button" class="btn btn-primary form-control" onclick="javascript:form_requestLent.submit();"><i class="fas fa-check"></i> リクエストする。</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    @if ($opacs_books->lent_flag == 1 || $opacs_books->lent_flag == 2) 
        <form action="/plugin/opacs/returnLent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" id="form_returnLent" name="form_returnLent" method="POST">
            {{ csrf_field() }}
            <h4><span class="badge badge-primary">返却</span></h4>

            <div class="form-group">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="control-label">学籍番号</label><label class="badge badge-danger">必須</label>
                        <input type="text" name="return_student_no" value="{{old('return_student_no')}}" class="form-control">
                        @if ($errors && $errors->has('return_student_no')) <div class="text-danger">{{$errors->first('return_student_no')}}</div> @endif
                    </div>
                    <div class="col-sm-4">
                        <label class="control-label">返却日</label><label class="badge badge-danger">必須</label>

                        <div class="input-group date" id="return_date" data-target-input="nearest">
                            <input type="text" name="return_date" value="{{old('return_date')}}" class="form-control datetimepicker-input" data-target="#return_date"/>
                            <div class="input-group-append" data-target="#return_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        @if ($errors && $errors->has('return_date')) <div class="text-danger">{{$errors->first('return_date')}}</div> @endif
                        <script type="text/javascript">
                            $(function () {
                                $('#return_date').datetimepicker({
                                    locale: 'ja',
                                    dayViewHeaderFormat: 'YYYY年 M月',
                                    format: 'YYYY/MM/DD'
                                });
                            });
                        </script>
                    </div>
                    <div class="col-sm-3">
                        <label class="control-label">返却</label>
                        <button type="submit" class="btn btn-primary form-control"><i class="fas fa-check"></i> 返しました。</button>
                    </div>
                </div>
            </div>
        </form>
    @endif

@else
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-circle"></i>
        貸し出し操作、返却、貸し出しリクエストはログインすると行えます。
    </div>
@endauth

{{-- 一覧へ戻る --}}
<p class="text-center" style="margin-top: 16px;">
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
</p>
