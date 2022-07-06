{{--
 * Page 管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
@php
use App\Models\Common\Page;
@endphp

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
            {{-- 表示切り替え用フォームのsubmit JavaScript --}}
            function submit_toggle_display( source_id ) {
                form_toggle_display.action = form_toggle_display.action + "/" + source_id;
                form_toggle_display.submit();
            }
        </script>

        {{-- ページの上移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/sequenceUp')}}" method="POST" name="form_sequence_up" id="form_sequence_up" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="seq_method" value="sequence_up">
            <!input type="hidden" name="id" value="">
        </form>

        {{-- ページの下移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/sequenceDown')}}" method="POST" name="form_sequence_down" id="form_sequence_down" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="seq_method" value="sequence_down">
            <!input type="hidden" name="id" value="">
        </form>

        {{-- ページの指定場所移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/movePage')}}" method="POST" name="form_move_page" id="form_move_page" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="destination_id" value="">
        </form>

        {{-- 表示切り替え用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/toggleDisplay')}}" method="POST" name="form_toggle_display" id="form_toggle_display" class="form-horizontal">
            {{ csrf_field() }}
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
                @if (config('connect.USE_CONTAINER_BETA'))
                    <th nowrap class="pl-1"><i class="fas fa-box" title="コンテナページ"></i></th>
                @endif
                <th nowrap class="text-center"><i class="fas fa-users" title="ページ権限設定"></i></th>
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
                <tr id="{{$page_item->id}}">
                    @php
                    // 自分のページから親を遡って取得（＋トップページ）
                    $page_tree = $page_item->getPageTreeByGoingBackParent(null);
                    @endphp
                    <td class="table-text p-1" nowrap>
                        <div class="btn-group">
                            <a href="{{url('/manage/page/edit')}}/{{$page_item->id}}" class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span>編集</span></a>
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{url('/manage/page/role')}}/{{$page_item->id}}" >ページ権限設定</a>
                                <a class="dropdown-item" href="{{url('/manage/page/migrationOrder')}}/{{$page_item->id}}" >外部ページインポート</a>
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
                            <div class="mr-1"><a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="submit_toggle_display({{$page_item->id}});"><i class="far fa-eye" title="メニューに表示する"></i></a></div>
                        @else
                            <div class="mr-1"><a href="javascript:void(0);" class="btn btn-outline-primary btn-sm" onclick="submit_toggle_display({{$page_item->id}});"><i class="far fa-eye-slash" title="メニューから隠す"></i></a></div>
                        @endif
                    </td>
                    <td class="table-text p-1" nowrap>
                        <div><a href="{{url($page_item->permanent_link)}}">{{ $page_item->permanent_link }}</a></div>
                    </td>
                    <td class="table-text p-1">
                        @if($page_item->password)
                            <i class="fas fa-key" title="閲覧パスワードあり"></i>
                        @else

                            @php
                            $password_parent = null;
                            // 自分及び先祖ページを遡る
                            foreach ($page_tree as $page_tmp) {
                                if ($page_tmp->password) {
                                    $password_parent = $page_tmp->password;
                                    break;
                                }
                            }
                            @endphp
                            @if ($password_parent)
                                <i class="fas fa-key text-warning" title="閲覧パスワードあり(親ページを継承)"></i>
                            @endif

                        @endif
                    </td>
                    <td class="table-text p-1">
                        @if($page_item->membership_flag == 1)
                            <i class="fas fa-lock text-danger" title="メンバーシップページ"></i>
                        @elseif($page_item->membership_flag == 2)
                            <i class="fas fa-sign-out-alt text-danger" title="ログインユーザ全員参加"></i>
                        @else

                            @php
                            $membership_flag_parent = 0;
                            // 自分及び先祖ページを遡る
                            foreach ($page_tree as $page_tmp) {
                                if ($page_tmp->membership_flag) {
                                    $membership_flag_parent = $page_tmp->membership_flag;
                                    break;
                                }
                            }
                            @endphp
                            @if($membership_flag_parent == 1)
                                <i class="fas fa-lock text-warning" title="メンバーシップページ(親ページを継承)"></i>
                            @elseif($membership_flag_parent == 2)
                                <i class="fas fa-sign-out-alt text-warning" title="ログインユーザ全員参加(親ページを継承)"></i>
                            @else
                                <i class="fas fa-lock-open" title="公開ページ"></i>
                            @endif

                        @endif
                    </td>
                    @if (config('connect.USE_CONTAINER_BETA'))
                        <td class="table-text p-1">
                            @if($page_item->container_flag == 1)
                                <i class="fas fa-box" title="コンテナページ"></i>
                            @else
                                @php
                                $container_flag_parent = 0;
                                // 自分及び先祖ページを遡る
                                foreach ($page_tree as $page_tmp) {
                                    if ($page_tmp->container_flag) {
                                        $container_flag_parent = $page_tmp->container_flag;
                                        break;
                                    }
                                }
                                @endphp
                                @if($container_flag_parent == 1)
                                    <i class="fas fa-box text-warning" title="コンテナページ(親ページを継承)"></i>
                                @endif
                            @endif
                        </td>
                    @endif
                    <td class="table-text p-1 text-center" nowrap>
                        @if ($page_item->page_roles->isEmpty())

                            @php
                            // 自分及び先祖ページを遡る
                            $page_roles_parent = collect();
                            foreach ($page_tree as $page_tmp) {
                                if (! $page_tmp->page_roles->isEmpty()) {
                                    $page_roles_parent = $page_tmp->page_roles;
                                    break;
                                }
                            }
                            @endphp
                            @if ($page_roles_parent->isEmpty())
                                <a href="{{url('/manage/page/role')}}/{{$page_item->id}}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-users" title="ページ権限設定"></i> <span class="badge badge-light">権限なし</span>
                                </a>
                            @else
                                <a href="{{url('/manage/page/role')}}/{{$page_item->id}}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-users" title="ページ権限設定"></i> <span class="badge badge-light">親を継承</span>
                                </a>
                            @endif

                        @else
                            <a href="{{url('/manage/page/role')}}/{{$page_item->id}}" class="btn btn-success btn-sm">
                                <i class="fas fa-users" title="ページ権限設定"></i> <span class="badge badge-light">権限あり</span>
                            </a>
                        @endif
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
                            <div><img src="{{asset('/images/core/layout/' . $page_item->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$page_item->getLayoutTitle()}}"></div>
                        @else

                            @php
                            $layout_page_parent = new Page();
                            // 自分及び先祖ページを遡る
                            foreach ($page_tree as $page_tmp) {
                                if ($page_tmp->getSimpleLayout()) {
                                    $layout_page_parent = $page_tmp;
                                    break;
                                }
                            }
                            @endphp
                            @if ($layout_page_parent->getSimpleLayout())
                                <div class="border border-warning"><img src="{{asset('/images/core/layout/' . $layout_page_parent->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$layout_page_parent->getLayoutTitle()}}（親ページを継承）"></div>
                            @else
                                <div></div>
                            @endif

                        @endif
                    </td>
                    <td class="table-text p-1 text-center">
                        <div>@if($page_item->othersite_url_target)<i class="fas fa-window-restore" title="新ウィンドウ"></i>@endif</div>
                    </td>
                    <td class="table-text p-1 text-center">
                        @if ($page_item->ip_address)
                            <div><i class="fas fa-network-wired" title="{{$page_item->ip_address}}"></i></div>
                        @else
                            @php
                            $ip_address_page_parent = new Page();
                            // 自分及び先祖ページを遡る
                            foreach ($page_tree as $page_tmp) {
                                if ($page_tmp->ip_address) {
                                    $ip_address_page_parent = $page_tmp;
                                    break;
                                }
                            }
                            @endphp
                            @if ($ip_address_page_parent->ip_address)
                                <div><i class="fas fa-network-wired text-warning" title="{{$ip_address_page_parent->ip_address}}(親ページを継承)"></i></div>
                            @else
                                <div></div>
                            @endif
                        @endif
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
