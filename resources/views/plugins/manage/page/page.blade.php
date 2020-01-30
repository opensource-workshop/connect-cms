{{--
 * Page 管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card mb-1">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.page.page_manage_tab')

</div>
</div>

{{-- 入力フォーム --}}
@include('plugins.manage.page.page_form')

<!-- Pages list -->
@if (count($pages) > 0)
    <div class="card mt-3">
        <div class="card-header">ページ一覧</div>

        <script type="text/javascript">
            {{-- ページの上移動用フォームのsubmit JavaScript --}}
            function submit_sequence_up( id ) {
                form_sequence_up.action = form_sequence_up.action + "/" + id;
                form_sequence_up.submit();
            }

            {{-- ページの下移動用フォームのsubmit JavaScript --}}
            function submit_sequence_down( id ) {
                form_sequence_down.action = form_sequence_down.action + "/" + id;
                form_sequence_down.submit();
            }

            {{-- ページの指定場所移動用フォームのsubmit JavaScript --}}
            function submit_move_page( source_id ) {
                form_move_page.action = form_move_page.action + "/" + source_id;
                //var select_name = "form_select_page" + source_id;
                obj = document.forms["form_select_page" + source_id];
                index = obj.select_page.selectedIndex;
                if (index != 0){
                    //form_move_page.source_id.value = source_id;
                    form_move_page.destination_id.value = obj.select_page.options[index].value;
                    form_move_page.submit();
                }
            }
        </script>

        {{-- ページの上移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/sequence_up')}}" method="POST" name="form_sequence_up" id="form_sequence_up" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="seq_method" value="sequence_up">
            <!input type="hidden" name="id" value="">
        </form>

        {{-- ページの下移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/sequence_down')}}" method="POST" name="form_sequence_down" id="form_sequence_down" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="seq_method" value="sequence_down">
            <!input type="hidden" name="id" value="">
        </form>

        {{-- ページの指定場所移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/move_page')}}" method="POST" name="form_move_page" id="form_move_page" class="form-horizontal">
            {{ csrf_field() }}
{{--            <input type="hidden" name="source_id" value=""> --}}
            <input type="hidden" name="destination_id" value="">
        </form>

        <div class="card table-responsive">
            <table class="table table-striped cc-font-90">
            <thead>
                <th></th>
                <th nowrap>移動先</th>
                <th nowrap>ページ名</th>
                <th nowrap class="pl-1"><i class="far fa-eye"></i></th>
                <th nowrap>固定リンク</th>
{{--
                <th nowrap>背景色</th>
                <th nowrap>ヘッダー</th>
                <th nowrap>レイアウト</th>
--}}
                <th nowrap>背</th>
                <th nowrap>ヘ</th>
                <th nowrap>レ</th>
                <th nowrap>新</th>
                <th nowrap>IP</th>
                <th nowrap>外</th>
                <th nowrap>ク</th>
            </thead>
            <tbody>
                @foreach($pages as $page_item)
                <tr>
                    <!-- Task Name -->
                    <td class="table-text col-md-2 p-1" nowrap>
                        <a href="{{url('/manage/page/edit')}}/{{$page_item->id}}" class="btn btn-primary btn-sm"><i class="far fa-edit"></i> <span>編集</span></a>

                        {{-- 上移動 --}}
                        <button type="button" class="btn p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$page_item->id}})">
                            <i class="fas fa-arrow-up"></i>
                        </button>

                        {{-- 下移動 --}}
                        <button type="button" class="btn p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$page_item->id}})">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </td>
                    <td class="table-text col-md-2 p-1">
                        {{-- 指定場所移動 --}}
                        <form name="form_select_page{{$page_item->id}}" id="form_select_page{{$page_item->id}}" class="form-horizontal">
                            <select name="select_page" class="manage-page-selectpage" onChange="submit_move_page({{$page_item->id}});">
                                <option value="">...</option>
                                <option value="0">最上位の階層へ</option>
                                @foreach($pages_select as $page_select)
                                    {{-- 自分自身 or 子孫のノードは選択不可にする --}}
                                    <option value="{{$page_select->id}}"@if ($page_item->id == $page_select->id or $page_select->isDescendantOf($page)) disabled style="background-color: #f0f0f0;"@endif>
                                        @for ($i = 0; $i < $page_select->depth; $i++)
                                        -
                                        @endfor
                                        {{$page_select->page_name}}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="table-text p-1 manage-page-pagename">
                        {{-- 各ページの深さをもとにインデントの表現 --}}
                        @for ($i = 0; $i < $page_item->depth; $i++)
                            @if ($i+1==$page_item->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                        @endfor
                        {{$page_item->page_name}}{{-- ページ名 --}}
                    </td>
                    <td class="table-text p-1">
                        @if ($page_item->base_display_flag == 1)
                            <div><i class="far fa-eye"></i></div>
                        @else
                            <div><i class="far fa-eye-slash"></i></div>
                        @endif
                    </td>
                    <td class="table-text p-1" nowrap>
                        <div><a href="{{url($page_item->permanent_link)}}">{{ $page_item->permanent_link }}</a></div>
                    </td>
                    <td class="table-text p-1">
                        {{-- <div>{{ $page_item->background_color }}</div> --}}
                        <div>@if($page_item->background_color)<i class="fas fa-exclamation-circle"></i>@endif</div>
                    </td>
                    <td class="table-text p-1">
                        {{-- <div>{{ $page_item->header_color }}</div> --}}
                        <div>@if($page_item->header_color)<i class="fas fa-exclamation-circle"></i>@endif</div>
                    </td>
                    <td class="table-text p-1">
                        <div>{{ $page_item->layout }}</div>
                    </td>
                    <td class="table-text p-1">
                        <div>@if($page_item->othersite_url_target)<i class="fas fa-check"></i>@endif</div>
                    </td>
                    <td class="table-text p-1">
                        <div>@if($page_item->ip_address)<i class="fas fa-exclamation-circle"></i>@endif</div>
                    </td>
                    <td class="table-text p-1">
                        <div>@if($page_item->othersite_url)<i class="fas fa-exclamation-circle"></i>@endif</div>
                    </td>
                    <td class="table-text p-1">
                        <div>{{$page_item->class}}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
