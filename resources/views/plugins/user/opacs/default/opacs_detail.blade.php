{{--
 * 書誌データ詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}

<table class="table table-bordered cc_responsive_table">
<thead>
<tr class="active">
    <th colspan="2">書籍情報</th>
</tr>
</thead>
<tbody>
<tr>
    <th>ISBN等</th>
    <td>{{$opacs_books->isbn}}</td>
</tr>
<tr>
    <th>タイトル</th>
    <td>{{$opacs_books->title}}</td>
</tr>
<tr>
    <th>請求記号</th>
    <td>{{$opacs_books->ndc}}</td>
</tr>
<tr>
    <th>著者</th>
    <td>{{$opacs_books->creator}}</td>
</tr>
<tr>
    <th>出版者</th>
    <td>{{$opacs_books->publisher}}</td>
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

    <h4><span class="badge badge-primary">郵送貸し出しリクエスト</span></h4>

    <div class="form-group">

        @if ($opacs_books->lent_flag == 1) 
            <div class="alert alert-warning" style="margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は現在、貸し出し中のため、郵送貸し出しリクエストはできません。
            </div>
        @elseif ($opacs_books->lent_flag == 2) 
            <div class="alert alert-warning" style="margin-top: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は現在、貸し出しリクエスト中のため、郵送貸し出しリクエストはできません。＜管理者メニュー＞
            </div>
        @else
            <form action="/plugin/opacs/requestLent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#{{$frame_id}}" id="form_requestLent" name="form_requestLent" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-4" style="margin-top: 8px;">
                        <label class="control-label">学籍番号</label><label class="badge badge-danger">必須</label>
                        <input type="text" name="student_no" value="{{old('student_no')}}" class="form-control">
                        @if ($errors && $errors->has('student_no')) <div class="text-danger">{{$errors->first('student_no')}}</div> @endif
                    </div>


                    <div class="col-sm-4" style="margin-top: 8px;">
                        <label class="control-label">返却予定日</label><label class="badge badge-danger">必須</label>

                        <div class="input-group date" id="return_scheduled_req" data-target-input="nearest">
                            <input type="text" name="return_scheduled" value="{{old('return_scheduled')}}" class="form-control datetimepicker-input" data-target="#return_scheduled_req"/>
                            <div class="input-group-append" data-target="#return_scheduled_req" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        @if ($errors && $errors->has('return_scheduled')) <div class="text-danger">{{$errors->first('return_scheduled')}}</div> @endif
                        <script type="text/javascript">
                            $(function () {
                                $('#return_scheduled_req').datetimepicker({
                                    locale: 'ja',
                                    dayViewHeaderFormat: 'YYYY年 M月',
                                    format: 'YYYY/MM/DD'
                                });
                            });
                        </script>
                    </div>

                    {{--
                        <div class="input-group date" data-provide="datepicker">
                            <input type="text" class="form-control datepicker" id='date_sample'><span class="input-group-addon"><i class="fas fa-th"></i></span>
                        </div>
                        <script type="text/javascript">
                            $('.datepicker').datepicker({
                                todayHighlight: true,
                                language:'ja'
                            });
                        </script>
                    --}}

                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="control-label" style="margin-top: 8px;">連絡先電話番号</label>
                        <input type="text" name="phone_no" value="" class="form-control">
                    </div>
                    <div class="col-sm-4">
                        <label class="control-label" style="margin-top: 8px;">連絡先メールアドレス</label>
                        <input type="text" name="email" value="" class="form-control">
                    </div>
                    <div class="col-sm-3">
                        <label class="control-label" style="margin-top: 8px;">返却</label>
                        <button type="button" class="btn btn-primary form-control" onclick="javascript:form_requestLent.submit();"><i class="fas fa-check"></i> リクエストする。</button>
                    </div>
                </div>
            @endif
        </div>
    </form>

<h4><span class="badge badge-primary">貸し出し</span></h4>

    @if ($opacs_books->lent_flag == 1) 
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
    <div class="row">
        <div class="col-sm-4">
            <label class="control-label">学籍番号</label>
            <input type="text" name="student_no" value="" class="form-control">
        </div>
        <div class="col-sm-4">
            <label class="control-label">返却予定日</label>

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
            <button type="submit" class="btn btn-primary form-control"><i class="fas fa-check"></i> 借りました。</button>
        </div>
    </div>
</div>

    @endif

    @if ($opacs_books->lent_flag) 
<h4><span class="label label-primary">返却</span></h4>

<div class="form-group">
    <div class="row">
        <div class="col-sm-4">
            <label class="control-label">学籍番号</label>
            <input type="text" name="student_no" value="" class="form-control">
        </div>
        <div class="col-sm-4">
            <label class="control-label">返却日</label>
    <div class="input-group date" data-provide="datepicker">
                <input type="text" name="return_date" value="" class="form-control datepicker"><span class="input-group-addon"><i class="fas fa-th"></i></span>
    </div>
        </div>
        <div class="col-sm-3">
            <label class="control-label">返却</label>
            <button type="submit" class="btn btn-primary form-control"><i class="fas fa-check"></i> 返しました。</button>
        </div>
    </div>
</div>

    @endif

@else
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        貸し出し操作、返却、貸し出しリクエストはログインすると行えます。
    </div>
@endauth
