{{--
 * Opac 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opac・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- フラッシュメッセージ --}}
@if (session('search_opacs'))
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('search_opacs') }}
    </div>
@endif

{{-- OPAC 検索条件エリア --}}

<form action="{{url('/')}}/redirect/plugin/opacs/search/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_search" name="form_search" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="opac_search_type" @if(isset($opac_search_type)) value="{{$opac_search_type}}" @else value="1" @endif />
    <div class="form-group">
        <div class="row">
            <div class="col-sm-8">
                <div class="input-group date">
                    <input type="text" name="keyword" value="{{Session::get('search_keyword.'.$frame_id)}}" class="form-control" placeholder="キーワード検索">
                    <span class="input-group-btn">
                        <button type="submit" onclick="javascript: document.form_search.opac_search_type.value=1" class="btn btn-primary"><i class="fas fa-search" aria-label="検索" role="presentation"></i></button>
                    </span>
                </div>
                <div class="row ml-0 mt-1">
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
                    <div class="card border">
                        <div class="card-body bg-danger">
                            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するOPACを選択するか、作成してください。</p>
                        </div>
                    </div>
                @endif
            @endcan

            </div>
        </div>
    </div>

    <!-- 詳細検索条件画面 -->
    <div class="card mb-4">
        <div class="card-header" id="user_search_condition">
            <a data-toggle="collapse" href="#search_collapse" role="button" @if (session('opac_search_condition.'.$frame_id)) aria-expanded="true" @else  aria-expanded="false" @endif aria-controls="search_collapse">詳細検索条件（中間一致）</a>
        </div>
        <div id="search_collapse" class="collapse @if (session('opac_search_condition.'.$frame_id)) show @endif " aria-labelledby="user_search_condition" data-parent="#search_collapse">
            <div class="card-body">

                <div class="form-group row">
                    <label for="opac_search_condition_title" class="col-md-2 col-form-label text-md-right">タイトル</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[title]" id="opac_search_condition_title" @if (session('opac_search_condition.'.$frame_id)) value="{{Session::get('opac_search_condition.'.$frame_id)['title']}}" @endif class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_name" class="col-md-2 col-form-label text-md-right">ISBN</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[isbn]" id="opac_search_condition_isbn" @if (session('opac_search_condition.'.$frame_id)) value="{{Session::get('opac_search_condition.'.$frame_id)['isbn']}}" @endif class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_creator" class="col-md-2 col-form-label text-md-right">著者</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[creator]" id="opac_search_condition_creator" @if (session('opac_search_condition.'.$frame_id)) value="{{Session::get('opac_search_condition.'.$frame_id)['creator']}}" @endif class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_ndc" class="col-md-2 col-form-label text-md-right">NDC</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[ndc]" id="opac_search_condition_ndc" @if (session('opac_search_condition.'.$frame_id)) value="{{Session::get('opac_search_condition.'.$frame_id)['ndc']}}" @endif class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_publisher" class="col-md-2 col-form-label text-md-right">出版社</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[publisher]" id="opac_search_condition_publisher" @if (session('opac_search_condition.'.$frame_id)) value="{{Session::get('opac_search_condition.'.$frame_id)['publisher']}}" @endif class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="opac_search_condition_publication_year" class="col-md-2 col-form-label text-md-right">出版年</label>
                    <div class="col-md-10">
                        <input type="text" name="opac_search_condition[publication_year]" id="opac_search_condition_publication_year" @if (session('opac_search_condition.'.$frame_id)) value="{{Session::get('opac_search_condition.'.$frame_id)['publication_year']}}" @endif class="form-control">
                    </div>
                </div>

                <div class="form-group text-center">
                    <div class="row">
                        <div class="mx-auto">
                            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/redirect/plugin/opacs/searchDetailClear/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'">
                                <i class="fas fa-times"></i> 詳細条件クリア
                            </button>
                            <button type="submit" onclick="javascript: document.form_search.opac_search_type.value=2" class="btn btn-primary form-horizontal">
                                <i class="fas fa-check"></i> 詳細検索実行
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        #user_search_condition a[aria-expanded="false"]:after {
            content: "▼";
        }
        #user_search_condition a[aria-expanded="true"]:after {
            content: "▲";
        }
    </style>

    @if (isset($opacs_books))
    <div class="form-group form-row mb-3">
        <div class="col-md-7">
            {{-- 並び替え --}}
            <select class="form-control" name="opac_search_sort_type" onChange="javascript:submit(this.form);" aria-describedby="sort_type{{$frame_id}}">
                <option value="">並び替え</option>
                <option value="1" @if($sort_type=="1") selected @endif>タイトル：昇順</option>
                <option value="2" @if($sort_type=="2") selected @endif>タイトル：降順</option>
                <option value="3" @if($sort_type=="3") selected @endif>著者：昇順</option>
                <option value="4" @if($sort_type=="4") selected @endif>著者：降順</option>
                <option value="5" @if($sort_type=="5") selected @endif>出版者：昇順</option>
                <option value="6" @if($sort_type=="6") selected @endif>出版者：降順</option>
            </select>
            <small class="form-text text-muted" id="sort_type{{$frame_id}}">選択すると自動的に並び順が変更されます。</small>
        </div>
        <div class="col-md-5">

            {{-- 表示件数変更 --}}
            <select class="form-control" name="opac_search_view_count" onChange="javascript:submit(this.form);" aria-describedby="view_count{{$frame_id}}">
                <option value="">表示件数</option>
                <option value="1" @if($opac_search_view_count=="1") selected @endif>1件</option>
                <option value="5" @if($opac_search_view_count=="5") selected @endif>5件</option>
                <option value="10" @if($opac_search_view_count=="10") selected @endif>10件</option>
                <option value="20" @if($opac_search_view_count=="20") selected @endif>20件</option>
                <option value="50" @if($opac_search_view_count=="50") selected @endif>50件</option>
                <option value="100" @if($opac_search_view_count=="100") selected @endif>100件</option>
            </select>
            <small class="form-text text-muted" id="view_count{{$frame_id}}">選択すると自動的に表示件数が変更されます。</small>
        </div>
    </div>
    @endif

</form>


{{-- 検索結果画面 --}}

@if (isset($opacs_books))
    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $opacs_books, 'frame' => $frame, 'aria_label_name' => $opac_frame->opac_name, 'class' => 'form-group'])

    <style type="text/css">
    <!--
    .book-list .table th, .book-list .table td { padding: 0.5em; }
    -->
    </style>

    <div class="book-list table-responsive">
    <table class="table">
    <thead>
        <tr>
            <th nowrap>貸</th>
            <th nowrap>タイトル</th>
            <th nowrap>著者</th>
            <th nowrap>出版者</th>
        </tr>
    </thead>
    <tbody>
    @foreach($opacs_books as $book)
        <tr>
            <td>@if ($book->lent_flag == 1 || $book->lent_flag == 2) <span style="color: red;"><i class="fas fa-user"></i></span> @endif</td>
            <td>
                @can("role_article")
                <a href="{{url('/')}}/plugin/opacs/edit/{{$page->id}}/{{$frame_id}}/{{$book->id}}#frame-{{$frame->id}}">
                    <i class="far fa-edit"></i>
                </a>
                @endcan
                <a href="{{url('/')}}/plugin/opacs/show/{{$page->id}}/{{$frame_id}}/{{$book->id}}">{{$book->title}}</a>
            </td>
            <td>{{$book->creator}}</td>
            <td>{{$book->publisher}}</td>
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
    @include('plugins.common.user_paginate', ['posts' => $opacs_books, 'frame' => $frame, 'aria_label_name' => $opac_frame->opac_name])

@endif

@endsection
