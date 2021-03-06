{{--
 * 書誌データ詳細画面テンプレート。
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

{{-- 書籍情報画面 --}}

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
    <th nowrap>配架場所</th>
    <td>{{$opacs_books->shelf}}</td>
</tr>
<tr>
    <th>状況</th>
    <td>
{{--
    @if ($opacs_books->lent_flag == 1)
        <span style="color: red;"><i class="fas fa-user"></i></span> 
        貸し出し中（返却予定日：@php echo date('Y年n月j日', strtotime($opacs_books->return_scheduled)); @endphp）
        </span>
    @elseif ($opacs_books->lent_flag == 2)
        <span style="color: red;"><i class="fas fa-user"></i></span> 
        貸し出しリクエスト中（返却予定日：@php echo date('Y年n月j日', strtotime($opacs_books->return_scheduled)); @endphp)
        </span>
    @endif
--}}
        @if (isset($opacs_book_lents))
            <span style="color: red;"><i class="fas fa-user"></i></span> 貸し出し中（返却予定日：@php echo date('Y年n月j日', strtotime($opacs_book_lents->return_scheduled)); @endphp）</span><br/>
        @endif
        @if (isset($opacs_book_lents_count) && $opacs_book_lents_count > 0)
            <span style="color: red;"><i class="fas fa-user"></i></span> 貸し出しリクエスト中（{{$opacs_book_lents_count}}件）</span>
        @endif
    </td>
</tr>
</table>

{{-- 貸出部分 --}}
@if($opac_frame->lent_setting != 0)
    @include('plugins.user.opacs.default.opacs_lent')
@endif

{{-- 一覧へ戻る --}}
{{-- 貸出部分へ移設
<p class="text-center" style="margin-top: 16px;">
    <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
</p>
--}}
@endsection
