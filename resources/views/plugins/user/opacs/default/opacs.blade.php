{{--
 * Opac画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opac・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- OPAC表示 --}}
@if (isset($opacs_books))

    <form action="/redirect/plugin/opacs/search/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_requestLent" name="form_requestLent" method="POST">
    {{ csrf_field() }}
    <div class="form-group">
        <div class="row">
            <div class="col-sm-6">
                <div class="input-group date">
                    <input type="text" name="keyword" value="{{Session::get('search_keyword')}}" class="form-control" placeholder="キーワード検索">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </span>
                </div>
            </div>
            <div class="col-sm-6">

            {{-- 新規登録 --}}
            @can("role_article")
                @if (isset($frame) && $frame->bucket_id)
                    <p class="text-right">
                        {{-- 新規登録ボタン --}}
                        <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/create/{{$page->id}}/{{$frame_id}}'">
                            <i class="fas fa-plus"></i> 新規登録
                        </button>
                    </p>
                @else
                    <div class="panel panel-default">
                        <div class="panel-body bg-danger">
                            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するOPACを選択するか、作成してください。</p>
                        </div>
                    </div>
                @endif
            @endcan

            </div>
        </div>
    </div>
    </form>

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $opacs_books->fragment('frame-'.$frame_id)->links() }}
    </div>

    <style type="text/css">
    <!--
    .book-list .table th, .book-list .table td { padding: 0.5em; }
    -->
    </style>

    <div class="book-list table-responsive">
    <table class="table">
    <thead>
        <tr>
{{--            <th nowrap>詳細</th> --}}
            <th nowrap>貸</th>
{{--            <th nowrap>ISBN等</th> --}}
            <th nowrap>タイトル</th>
{{--            <th nowrap>請求記号</th> --}}
            <th nowrap>著者</th>
            <th nowrap>出版者</th>
            <th nowrap>受入日付</th>
        </tr>
    </thead>
    <tbody>
    @foreach($opacs_books as $book)
        <tr>
{{--
            <td>
                <a href="{{url('/')}}/plugin/opacs/show/{{$page->id}}/{{$frame_id}}/{{$book->id}}">
                    <span class="label label-primary">詳細</span>
                </a>
            </td>
--}}
            <td>@if ($book->lent_flag == 1 || $book->lent_flag == 2) <span style="color: red;"><i class="fas fa-user"></i></span> @endif</td>
{{--
            <td nowrap>
                {{$book->isbn}}
            </td>
--}}
            <td>
                @can("role_article")
                <a href="{{url('/')}}/plugin/opacs/edit/{{$page->id}}/{{$frame_id}}/{{$book->id}}">
                    <i class="far fa-edit"></i>
                </a>
                @endcan
                <a href="{{url('/')}}/plugin/opacs/show/{{$page->id}}/{{$frame_id}}/{{$book->id}}">{{$book->title}}</a>
            </td>
{{--
            <td>{{$book->ndc}}</td>
--}}
            <td>{{$book->creator}}</td>
            <td>{{$book->publisher}}</td>
            <td>{{$book->accept_date}}</td>
        </tr>
    @endforeach
    </tbody>
    </table>
    </div>

    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-circle"></i>
        貸出、返却、リクエストはログイン後、各書籍の詳細画面から操作できます。
    </div>

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $opacs_books->fragment('frame-'.$frame_id)->links() }}
    </div>
@endif

{{-- 新規登録 --}}
{{--
@can("role_article")
    @if (isset($frame) && $frame->bucket_id)
        <p class="text-center" style="margin-top: 16px;">
--}}
            {{-- 新規登録ボタン --}}
{{--
            <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/create/{{$page->id}}/{{$frame_id}}'"><i class="fas fa-plus"></i> 新規登録</button>
        </p>
    @else
        <div class="panel panel-default">
            <div class="panel-body bg-danger">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するOPACを選択するか、作成してください。</p>
            </div>
        </div>
    @endif
@endcan
--}}
@endsection
