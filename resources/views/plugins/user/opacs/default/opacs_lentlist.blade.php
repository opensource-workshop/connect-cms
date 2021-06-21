{{--
 * 貸し出し中一覧画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<div class="alert alert-info" style="margin-top: 10px;">
    <i class="fas fa-exclamation-circle"></i>
    モデレータ用管理画面
</div>

{{-- メッセージ画面 --}}
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
        <div class="alert alert-primary">
        <i class="fas fa-exclamation-circle"></i>
        {{$message}}
    </div>
@endif


<div class="card mb-3">
    <div class="card-header" id="frame-{{$frame->id}}-requestlist">郵送貸し出しリクエスト中一覧</div>

    <style type="text/css">
    <!--
    .book-list .table th, .book-list .table td { padding: 0.5em; }
    -->
    </style>

    <div class="book-list table-responsive">
    <table class="table">
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
            <div class="form-group text-center mb-0 row">
                <!-- 貸出ボタン -->
                <div class="col-4 col-sm-3 col-md-2" style="margin-left: auto;">
                    <form action="{{url('/')}}/plugin/opacs/roleLent/{{$page->id}}/{{$frame_id}}/{{$books_request->opacs_books_id}}" method="GET">
                        <input type="hidden" name="req_lent_id" value="{{old('req_lent_id', $books_request->id)}}" class="form-control">
                        <input type="hidden" name="req_student_no" value="{{old('req_student_no', $books_request->student_no)}}" class="form-control">
                        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                            貸出
                        </button>
                    </form>
                </div>

                <!-- 郵送貸し出しリクエスト取り消しボタン -->
                <div class="col-4 col-sm-3 col-md-2">
                    <form action="{{url('/')}}/plugin/opacs/destroyRequest/{{$page->id}}/{{$frame_id}}/{{$books_request->opacs_books_id}}#frame-{{$frame->id}}-lentlist" method="POST">
                        <input type="hidden" name="req_student_no" value="{{old('req_student_no', $books_request->student_no)}}" class="form-control">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('貸し出しリクエストを取り消します。\nよろしいですか？')"><span class="glyphicon glyphicon-ok"></span><i class="fas fa-trash-alt"></i> 取消</button>
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

<div class="card mb-3">
<div class="card-header" id="frame-{{$frame->id}}-requestlist">貸し出し一覧</div>

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
            <td>{{$books_lent->title}}</td>
            <td>{{$books_lent->barcode}}</td>
            <td>{{$books_lent->student_no}}</td>
        </tr>
        @endforeach
    </tbody>
    </table>

    <!-- 返却エリア -->
{{--
    <form class="col-12 row mb-3" action="{{url('/')}}/plugin/opacs/returnLent/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
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
        <label class="col-12 col-sm-12 col-md-4 col-lg-3 text-md-right col-form-label " style="float: left;">返却用バーコード <label class="badge badge-danger">必須</label></label>
        <div class="col-12 col-sm-8 col-md-5 col-lg-7 input-group">
            <input class="col-12 form-control" type="text" name="return_barcode" value="{{old('return_barcode')}}" placeholder="バーコードエリア">
            @if ($errors && $errors->has('return_barcode')) <div class="text-danger col-12">{{$errors->first('return_barcode')}}</div> @endif
            <small class="col-12 text-muted">バーコードリーダーで読み込んでください。</small>
        </div>

        <!-- 返却ボタン -->
        <div class="col-12 col-sm-4 col-md-3 col-lg-2 text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                返却
            </button>
        </div>
    </form>


</div>
</div>

<!-- 一覧へ戻る -->
<p class="text-center" style="margin-top: 16px;">
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 戻る</button>
</p>


@endsection
