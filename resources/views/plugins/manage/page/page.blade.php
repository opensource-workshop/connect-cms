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

{{-- 入力フォーム --}}
@include('plugins.manage.page.page_form')

<!-- Pages list -->
@if (count($pages) > 0)
    <div class="panel panel-default">
        <div class="panel-heading">ページ一覧</div>

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

        <div class="panel-body table-responsive">
            <table class="table table-striped">
            <thead>
                <th></th>
                <th nowrap>移動先</th>
                <th nowrap>ページ名</th>
                <th nowrap>On</th>
                <th nowrap>固定リンク</th>
                <th nowrap>背景色</th>
                <th nowrap>ヘッダー</th>
                <th nowrap>レイアウト</th>
            </thead>
            <tbody>
                @foreach($pages as $page)
                <tr>
                    <!-- Task Name -->
                    <td class="table-text col-md-2" nowrap>
                        <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-edit"></span> <span>編集</span></a>

                        {{-- 上移動 --}}
                        <button type="button" class="btn btn-default btn-xs" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$page->id}})">
                            <span class="glyphicon glyphicon-arrow-up"></span>
                        </button>

                        {{-- 下移動 --}}
                        <button type="button" class="btn btn-default btn-xs" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$page->id}})">
                            <span class="glyphicon glyphicon-arrow-down"></span>
                        </button>
                    </td>
                    <td class="table-text col-md-2">
                        {{-- 指定場所移動 --}}
                        <form name="form_select_page{{$page->id}}" id="form_select_page{{$page->id}}" class="form-horizontal">
                            <select name="select_page" onChange="submit_move_page({{$page->id}});">
                                <option value="">...</option>
                                <option value="0">最上位の階層へ</option>
                                @foreach($pages_select as $page_select)
                                    {{-- 自分自身 or 子孫のノードは選択不可にする --}}
                                    <option value="{{$page_select->id}}"@if ($page->id == $page_select->id or $page_select->isDescendantOf($page)) disabled style="background-color: #f0f0f0;"@endif>
                                        @for ($i = 0; $i < $page_select->depth; $i++)
                                        -
                                        @endfor
                                        {{$page_select->page_name}}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="table-text" nowrap>
                        {{-- 各ページの深さをもとにインデントの表現 --}}
                        @for ($i = 0; $i < $page->depth; $i++)
                            <span @if ($i+1==$page->depth) class="glyphicon glyphicon-chevron-right" style="color: #c0c0c0;"@else style="padding-left:15px;"@endif></span>
                        @endfor
                        {{$page->page_name}}{{-- ページ名 --}}
                    </td>
                    <td class="table-text">
                        @if ($page->base_display_flag == 1)
                            <div><span class="glyphicon glyphicon-ok"></div>
                        @else
                            <div><span class="glyphicon glyphicon-remove"></div>
                        @endif
                    </td>
                    <td class="table-text">
                        <div>{{ $page->permanent_link }}</div>
                    </td>
                    <td class="table-text">
                        <div>{{ $page->background_color }}</div>
                    </td>
                    <td class="table-text">
                        <div>{{ $page->header_color }}</div>
                    </td>
                    <td class="table-text">
                        <div>{{ $page->layout }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
