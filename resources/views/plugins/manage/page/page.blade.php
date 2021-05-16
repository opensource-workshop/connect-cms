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

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.page.page_manage_tab')
    </div>

    <!-- Pages list -->
    @if (count($pages) > 0)
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
            <input type="hidden" name="destination_id" value="">
        </form>

        <div class="table-responsive">
            <table class="table table-striped cc-font-90">
            <thead>
                <th></th>
                <th nowrap>移動先</th>
                <th nowrap>ページ名</th>
                <th nowrap class="pl-1"><i class="far fa-eye" title="メニュー表示"></i></th>
                <th nowrap>固定リンク</th>
                <th nowrap class="pl-1"><i class="fas fa-key" title="閲覧パスワードあり"></i></th>
                <th nowrap class="pl-1"><i class="fas fa-lock" title="メンバーシップページ・ログインユーザ全員参加"></i></th>
                <th nowrap class="pl-1"><i class="fas fa-users" title="ページ権限設定"></i></th>
                <th nowrap><i class="fas fa-paint-roller" title="背景色"></i></th>
                <th nowrap><img src="{{asset('/images/core/layout/header_icon.png')}}" title="ヘッダー色" class="cc-page-layout-icon" alt="ヘッダー色"></th>
                <th nowrap><img src="{{asset('/images/core/layout/1111.png')}}" class="cc-page-layout-icon" title="レイアウト" alt="レイアウト"></th>
                <th nowrap><i class="fas fa-window-restore" title="新ウィンドウ"></i></th>
                <th nowrap><i class="fas fa-network-wired" title="IPアドレス制限"></i></th>
                <th nowrap><i class="fas fa-external-link-alt" title="外部リンク"></i></th>
                <th nowrap><i class="fas fa-swatchbook" title="クラス名"></i></th>
            </thead>
            <tbody>
                @foreach($pages as $page_item)
                <tr>
                    <!-- Task Name -->
                    <td class="table-text p-1" nowrap>
                        <div class="btn-group">
                            <a href="{{url('/manage/page/edit')}}/{{$page_item->id}}" class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span>編集</span></a>
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{url('/manage/page/role')}}/{{$page_item->id}}" >ページ権限設定</a>
                                <a class="dropdown-item" href="{{url('/manage/page/migration_order')}}/{{$page_item->id}}" >外部ページインポート</a>
                            </div>
                        </div>

                        {{-- 上移動 --}}
                        <button type="button" class="btn p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_up({{$page_item->id}})">
                            <i class="fas fa-arrow-up"></i>
                        </button>

                        {{-- 下移動 --}}
                        <button type="button" class="btn p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_down({{$page_item->id}})">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </td>
                    <td class="table-text p-1">
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
                            <div><i class="far fa-eye" title="メニューに表示する"></i></div>
                        @else
                            <div><i class="far fa-eye-slash" title="メニューから隠す"></i></div>
                        @endif
                    </td>
                    <td class="table-text p-1" nowrap>
                        <div><a href="{{url($page_item->permanent_link)}}">{{ $page_item->permanent_link }}</a></div>
                    </td>
                    <td class="table-text p-1">
                        @if($page_item->password)
                            <i class="fas fa-key" title="閲覧パスワードあり"></i>
                        @endif
                    </td>
                    <td class="table-text p-1">
                        @if($page_item->membership_flag == 1)
                            <i class="fas fa-lock text-danger" title="メンバーシップページ"></i>
                        @elseif($page_item->membership_flag == 2)
                            <i class="fas fa-sign-out-alt text-danger" title="ログインユーザ全員参加"></i>
                        @else
                            <i class="fas fa-lock-open" title="公開ページ"></i>
                        @endif
                    </td>
                    <td class="table-text p-1">
                        <div><a href="{{url('/manage/page/role')}}/{{$page_item->id}}"><i class="fas fa-users" title="役割設定"></i></a></div>
                    </td>
                    <td class="table-text p-1 text-center">
                        @if($page_item->background_color)
                            <span class="border border-utils align-middle cc-page-layout-background" style="background-color:{{$page_item->background_color}};" title="{{$page_item->background_color}}"></span>
                        @endif
                    </td>
                    <td class="table-text p-1 text-center">
                        @if($page_item->header_color)
                            <span class="border border-utils align-middle cc-page-layout-background" style="background-color:{{$page_item->header_color}};" title="{{$page_item->header_color}}"></span>
                        @endif
                    </td>
                    <td class="table-text p-1 text-center">
                        @if ($page_item->getSimpleLayout())
                            <div><img src="{{asset('/images/core/layout/' . $page_item->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$page_item->getLayoutTitle()}}" alt="{{$page_item->getLayoutTitle()}}"></div>
                        @else
                            <div></div>
                        @endif
                    </td>
                    <td class="table-text p-1 text-center">
                        <div>@if($page_item->othersite_url_target)<i class="fas fa-window-restore" title="新ウィンドウ"></i>@endif</div>
                    </td>
                    <td class="table-text p-1 text-center">
                        <div>@if($page_item->ip_address)<i class="fas fa-network-wired" title="{{$page_item->ip_address}}"></i>@endif</div>
                    </td>
                    <td class="table-text p-1 text-center">
                        <div>@if($page_item->othersite_url)<i class="fas fa-external-link-alt" title="{{$page_item->othersite_url}}"></i>@endif</div>
                    </td>
                    <td class="table-text p-1 text-center">
                        <div>@if($page_item->class)<i class="fas fa-swatchbook" title="{{$page_item->class}}"></i>@endif</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    @endif
@endsection
