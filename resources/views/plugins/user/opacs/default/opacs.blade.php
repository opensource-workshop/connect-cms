{{--
 * Opac画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opac・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- メッセージ表示 --}}
@if ($messages)
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{$messages}}
    </div>
@endif

{{-- OPAC表示 --}}
{{--@if (isset($opacs_books)) --}}

<form action="{{url('/')}}/redirect/plugin/opacs/search/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_search" name="form_search" method="POST">
    {{ csrf_field() }}
    <div class="form-group">
        <div class="row">
            <div class="col-sm-8">
                <div class="input-group date">
                    <input type="text" name="keyword" value="{{Session::get('search_keyword.'.$frame_id)}}" class="form-control" placeholder="キーワード検索">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-label="検索" role="presentation"></i></button>
                    </span>
                </div>
                <div class="row ml-0 mt-1">
                    <a data-toggle="collapse" href="#search_collapse" role="button" aria-expanded="false" aria-controls="search_collapse">
                        詳細検索
                    </a>
                    <div class="ml-3"><a href="{{url('/')}}/redirect/plugin/opacs/searchClear/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">キーワードクリア</a></div>
                </div>
            </div>
            <div class="col-sm-4">

            {{-- 新規登録 --}}
            @can("role_article")
                @if (isset($frame) && $frame->bucket_id)
                    <p class="text-right">
                        {{-- 新規登録ボタン --}}
                        <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'">
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

<div class="collapse" id="search_collapse">
    <div class="card">
        <div class="card-header" id="user_search_condition">
            詳細検索条件（中間一致）
        </div>
        <div class="card-body">

            <form name="form_search_detail" id="form_search_detail" class="form-horizontal" method="post" action="{{url('/')}}/redirect/plugin/opacs/search/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
                {{ csrf_field() }}

                <div class="form-group row">
                    <label for="opac_search_condition_title" class="col-md-2 col-form-label text-md-right">タイトル</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[title]" id="opac_search_condition_title" value="" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_name" class="col-md-2 col-form-label text-md-right">ISBN</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[isbn]" id="opac_search_condition_isbn" value="" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_creator" class="col-md-2 col-form-label text-md-right">著者</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[creator]" id="opac_search_condition_creator" value="" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_ndc" class="col-md-2 col-form-label text-md-right">NDC</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[ndc]" id="opac_search_condition_ndc" value="" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_publisher" class="col-md-2 col-form-label text-md-right">出版社</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[publisher]" id="opac_search_condition_publisher" value="" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_publication_year" class="col-md-2 col-form-label text-md-right">出版年</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[publication_year]" id="opac_search_condition_publication_year" value="" class="form-control">
                    </div>
                </div>

                <div class="form-group text-center">
                    <div class="row">
                        <div class="mx-auto">
                            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/redirect/plugin/opacs/searchClear/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'">
                                <i class="fas fa-times"></i> 詳細条件クリア
                            </button>
                            <button type="submit" class="btn btn-primary form-horizontal">
                                <i class="fas fa-check"></i> 詳細検索実行
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if (isset($opacs_books))

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
                <a href="{{url('/')}}/plugin/opacs/show/{{$page->id}}/{{$frame_id}}/{{$book->id}}#frame-{{$frame->id}}">
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
                <a href="{{url('/')}}/plugin/opacs/edit/{{$page->id}}/{{$frame_id}}/{{$book->id}}#frame-{{$frame->id}}">
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
        {{-- 貸出、返却、リクエストはログイン後、各書籍の詳細画面から操作できます。 --}}
        貸出リクエストはログイン後、各書籍の詳細画面から操作できます。
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
            <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="fas fa-plus"></i> 新規登録</button>
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
