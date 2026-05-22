{{--
 * Page 管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
@php
use App\Enums\PageMetaRobots;
use App\Models\Common\Page;
use App\Models\Core\Configs;

$layout_default = config('connect.BASE_LAYOUT_DEFAULT');
$base_layout = Configs::getSharedConfigsValue('base_layout', $layout_default);
$base_layout = $base_layout ?: $layout_default;
$base_layout_page = new Page();
$base_layout_page->layout = $base_layout;
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
        <script src="{{ url('/') }}{{ mix('/js/manage/page/index.js') }}"></script>
        <script type="text/javascript">
            function save_page_manage_tree_state() {
                if (window.connectPageManageTree) {
                    window.connectPageManageTree.saveState();
                }
            }

            /** ページの上移動 */
            function submit_sequence_up(id) {
                save_page_manage_tree_state();
                form_sequence.action = "{{url('/manage/page/sequenceUp')}}/" + id;
                form_sequence.submit();
            }

            /** ページの下移動 */
            function submit_sequence_down(id) {
                save_page_manage_tree_state();
                form_sequence.action = "{{url('/manage/page/sequenceDown')}}/" + id;
                form_sequence.submit();
            }

            /** ページを一番上へ移動 */
            function submit_sequence_top(id) {
                save_page_manage_tree_state();
                form_sequence.action = "{{url('/manage/page/sequenceTop')}}/" + id;
                form_sequence.submit();
            }

            /** ページを一番下へ移動 */
            function submit_sequence_bottom(id) {
                save_page_manage_tree_state();
                form_sequence.action = "{{url('/manage/page/sequenceBottom')}}/" + id;
                form_sequence.submit();
            }

            {{-- ページの指定場所移動用フォームのsubmit JavaScript --}}
            function submit_move_page( source_id ) {
                form_move_page.action = form_move_page.action + "/" + source_id;
                //var select_name = "form_select_page" + source_id;
                obj = document.forms["form_select_page" + source_id];
                index = obj.select_page.selectedIndex;
                if (index != 0){
                    //form_move_page.source_id.value = source_id;
                    save_page_manage_tree_state();
                    form_move_page.destination_id.value = obj.select_page.options[index].value;
                    form_move_page.submit();
                }
            }

            {{-- 表示切り替え用フォームのsubmit JavaScript --}}
            function submit_toggle_display( source_id ) {
                save_page_manage_tree_state();
                form_toggle_display.action = form_toggle_display.action + "/" + source_id;
                form_toggle_display.submit();
            }

            let level_move_page_options = [];
            let level_move_page_option_map = {};
            let level_move_page_form_url = "{{url('/manage/page/movePage')}}";

            {{-- ページ移動モーダル画面でのjavascript --}}
            $(function(){
                if (window.connectPageManageTree) {
                    window.connectPageManageTree.init();
                }
                init_level_move_page_tree();
                {{-- 移動先決定ボタン --}}
                $('#moveLevelDoneBtn').on('click', function() {
                    let destination_id = $('input:radio[name="level_move_modal_page_id"]:checked').val();
                    if (destination_id) {
                        save_page_manage_tree_state();
                        $('#form_move_page_destination_id').val(destination_id);
                        $('#form_move_page_move_position').val($('input:radio[name="move_position"]:checked').val());
                        $('#form_move_page').submit();
                    }
                })
                {{-- 移動先選択ボタン --}}
                $('input:radio[name="level_move_modal_page_id"]').on('change', function() {
                    if ($(this).val() === '0') {
                        $('#move_position_before, #move_position_after').prop('disabled', true);
                        $('#move_position_child').prop('checked', true);
                    } else {
                        $('#move_position_before, #move_position_after').prop('disabled', false);
                    }
                    update_move_page_destination_text();
                    $('#moveLevelDoneBtn').prop('disabled', false);
                })
                {{-- 移動位置選択ボタン --}}
                $('input:radio[name="move_position"]').on('change', function() {
                    update_move_page_destination_text();
                })
                {{-- 移動先検索 --}}
                $('#levelMovePageSearch').on('input', function() {
                    update_level_move_page_search_results();
                })
            });

            {{-- 移動先ツリーのDOM参照と親子関係を初期化時にキャッシュする --}}
            function init_level_move_page_tree() {
                if (level_move_page_options.length) {
                    return;
                }

                $('.js-level-move-page-option').each(function() {
                    let element = this;
                    let row = {
                        element: element,
                        id: String($(element).attr('data-page-id')),
                        parent_id: String($(element).attr('data-parent-id') || ''),
                        search: String($(element).data('search') || '').toLowerCase(),
                        child_ids: [],
                    };

                    level_move_page_options.push(row);
                    level_move_page_option_map[row.id] = row;
                });

                level_move_page_options.forEach(function(row) {
                    if (!row.parent_id || !level_move_page_option_map[row.parent_id]) {
                        return;
                    }
                    level_move_page_option_map[row.parent_id].child_ids.push(row.id);
                });
            }

            {{-- 親子Mapから子孫ページIDを取得する --}}
            function collect_level_move_page_descendant_ids(page_id) {
                let row = level_move_page_option_map[page_id];
                if (!row || !row.child_ids.length) {
                    return [];
                }

                let descendant_ids = [];
                row.child_ids.forEach(function(child_id) {
                    descendant_ids.push(child_id);
                    descendant_ids = descendant_ids.concat(collect_level_move_page_descendant_ids(child_id));
                });

                return descendant_ids;
            }

            {{-- 移動先候補行の表示状態を更新する --}}
            function set_level_move_page_visible(row, visible) {
                row.element.style.display = visible ? '' : 'none';
            }

            {{-- 移動先検索結果の表示を更新する --}}
            function update_level_move_page_search_results() {
                let keyword = $('#levelMovePageSearch').val().toLowerCase();
                let matched_count = 0;

                level_move_page_options.forEach(function(row) {
                    let is_root_option = row.id === '0';
                    let is_visible = true;

                    is_visible = !keyword || is_root_option || row.search.indexOf(keyword) !== -1;

                    set_level_move_page_visible(row, is_visible);
                    if (is_visible && !is_root_option) {
                        matched_count++;
                    }
                });

                $('#levelMovePageNoResult').toggle(!!keyword && matched_count === 0);
            }
            {{-- 移動先と移動位置を組み合わせた確認文を更新する --}}
            function update_move_page_destination_text() {
                let destination = $('input:radio[name="level_move_modal_page_id"]:checked');
                if (!destination.length) {
                    $('.destination-page').text('');
                    return;
                }

                let move_position = $('input:radio[name="move_position"]:checked').val();
                if (destination.val() === '0') {
                    $('.destination-page').text('最上位の末尾');
                    return;
                }

                let destination_page_name = destination.attr('data-page-name');
                let position_label = '配下の末尾';
                if (move_position === 'before') {
                    position_label = '上';
                } else if (move_position === 'after') {
                    position_label = '下';
                }

                $('.destination-page').text(destination_page_name + ' の' + position_label);
            }
            {{-- ページ移動アイコンを押下した際にターゲットのページをセットする --}}
            function select_page(source_id, page_name) {
                // ページセット
                $('#form_move_page').attr('action', level_move_page_form_url + "/" + source_id);
                // テキストセット
                let page_name_txt = '<span class="source-page lead"></span> を <span class="destination-page lead"></span> へ移動します';
                $('.modal-title').html(page_name_txt);
                $('.source-page').text(page_name);
                $('#levelMoveSourcePageName').text(page_name);
                $('#levelMoveSourcePageNotice').show();
                // ラジオボタンを外す
                if ($('input:radio[name="level_move_modal_page_id"]:checked')[0]) {
                    $('input:radio[name="level_move_modal_page_id"]:checked')[0].checked = false;
                }
                // 移動位置を初期化
                $('#move_position_child').prop('checked', true);
                $('#move_position_before, #move_position_after').prop('disabled', false);
                $('#form_move_page_move_position').val('child');
                // 検索を初期化
                $('#levelMovePageSearch').val('');
                update_level_move_page_search_results();
                // 移動元ラベルを初期化
                $('.js-level-move-source-label').addClass('d-none');
                // 選択不可を解除
                $('input:radio[name="level_move_modal_page_id"]').prop('disabled', false);
                // 決定ボタンを無効化
                $('#moveLevelDoneBtn').prop('disabled', true);
                // 自分自身は選択不可にする
                $('#level_move_modal_page_' + source_id).prop('disabled', true);
                $('#level_move_source_label_' + source_id).removeClass('d-none');
                // 子孫のノードは選択不可にする
                let source_row = level_move_page_option_map[String(source_id)];
                if (source_row) {
                    collect_level_move_page_descendant_ids(source_row.id).forEach(function(descendant_id) {
                        $('#level_move_modal_page_' + descendant_id).prop('disabled', true);
                    });
                }
            }
        </script>

        {{-- ページ移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="" method="POST" name="form_sequence">
            {{ csrf_field() }}
            {{-- <input type="hidden" name="seq_method" value=""> --}}
        </form>

        {{-- ページの指定場所移動用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/movePage')}}" method="POST" name="form_move_page" id="form_move_page" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="destination_id" id="form_move_page_destination_id" value="">
            <input type="hidden" name="move_position" id="form_move_page_move_position" value="child">
        </form>

        {{-- 表示切り替え用フォーム(POSTのためのフォーム。一つ用意して一覧からJavascriptで呼び出し) --}}
        <form action="{{url('/manage/page/toggleDisplay')}}" method="POST" name="form_toggle_display" id="form_toggle_display" class="form-horizontal">
            {{ csrf_field() }}
        </form>

        @php
            $page_children = $pages->groupBy('parent_id');
        @endphp

        {{-- 階層変更用モーダル表示 --}}
        <div class="modal fade" id="moveLevlModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header pb-0">
                        <h4><div class="modal-title" style="font-size:1rem;">ページ移動</div></h4>
                    </div>
                    <div class="modal-body" style="max-height: 500px; overflow-y: scroll;">
                        <div id="levelMoveSourcePageNotice" class="alert alert-info py-2 mb-3" style="display:none;">
                            移動するページ：<span id="levelMoveSourcePageName"></span>
                        </div>
                        <div class="form-group mb-2">
                            <label for="levelMovePageSearch" class="small mb-1">移動先ページの検索</label>
                            <input type="search" id="levelMovePageSearch" class="form-control form-control-sm" placeholder="ページ名、固定リンクで検索">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small mb-1">移動位置</label>
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="mr-2">選択したページの</span>
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" value="child" id="move_position_child" name="move_position" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="move_position_child">配下</label>
                                </div>
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" value="before" id="move_position_before" name="move_position" class="custom-control-input">
                                    <label class="custom-control-label" for="move_position_before">上</label>
                                </div>
                                <div class="custom-control custom-radio mr-3">
                                    <input type="radio" value="after" id="move_position_after" name="move_position" class="custom-control-input">
                                    <label class="custom-control-label" for="move_position_after">下</label>
                                </div>
                                <span>に移動する</span>
                            </div>
                        </div>
                        <div class="pt-2 mt-2 border-top custom-control custom-radio custom-control-block js-level-move-page-option" data-page-id="0" data-parent-id="" data-search="最上位">
                            <input type="radio" value="0" id="level_move_modal_page_0" name="level_move_modal_page_id" data-page-name="最上位" class="custom-control-input">
                            <label class="custom-control-label" for="level_move_modal_page_0">最上位</label>
                        </div>
                        @foreach($pages_select as $page_item)
                            @php
                                $page_tree = $page_item->getPageTreeByGoingBackParent(null, false);
                                $page_path = $page_tree->reverse()->pluck('page_name')->implode(' > ');
                            @endphp
                            <div class="custom-control custom-radio custom-control-block js-level-move-page-option"
                                data-page-id="{{$page_item->id}}"
                                data-parent-id="{{$page_item->parent_id ?? ''}}"
                                data-search="{{$page_path}} {{$page_item->permanent_link}}">
                                <input type="radio" value="{{$page_item->id}}" id="level_move_modal_page_{{$page_item->id}}" data-page-name="{{$page_item->page_name}}" name="level_move_modal_page_id" class="custom-control-input">
                                @for ($i = 0; $i < $page_item->depth; $i++)
                                    @if ($i+1==$page_item->depth) <span class="px-3"></span> @else <span class="px-2"></span>@endif
                                @endfor
                                <label class="custom-control-label" for="level_move_modal_page_{{$page_item->id}}" id="level_move_page_{{$page_item->id}}">
                                    {{$page_item->page_name}}
                                    <span id="level_move_source_label_{{$page_item->id}}" class="badge badge-info ml-1 js-level-move-source-label d-none">移動するページ</span>
                                    @if ($page_item->base_display_flag == 0)
                                        <i class="far fa-eye-slash text-muted ml-1" title="メニューから隠す" aria-label="メニューから隠す"></i>
                                    @endif
                                    <span class="small text-muted ml-1">{{$page_item->permanent_link}}</span>
                                </label>
                            </div>
                        @endforeach
                        <div id="levelMovePageNoResult" class="text-muted" style="display:none;">該当するページがありません。</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                        <button id="moveLevelDoneBtn" type="button" class="btn btn-primary" disabled>決定</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="cc-table-scroll js-cc-table-scroll" data-page-manage-tree>
            <div class="cc-table-scroll__sticky">
                <div class="cc-table-scroll__top" aria-hidden="true">
                    <div class="cc-table-scroll__top-inner"></div>
                </div>
                <div class="cc-table-scroll__header" aria-hidden="true"></div>
            </div>
            <div class="table-responsive cc-table-scroll__body">
                <table class="table table-striped cc-font-90 mb-0 cc-table-sticky-header">
            <thead>
                <tr>
                    <th></th>
                    <th nowrap><i class="fas fa-sitemap" title="ページ移動" alt="ページ移動"></i></th>
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
                    <th nowrap><i class="fas fa-robot" title="検索避け設定"></i></th>
                    <th nowrap><i class="fas fa-swatchbook" title="クラス名"></i></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pages as $page_item)
                @php
                    $direct_children = $page_children->get($page_item->id, collect());
                    $has_children = $direct_children->isNotEmpty();
                    $page_tree = $page_item->getPageTreeByGoingBackParent(null, false);
                    $ancestor_ids = $page_tree->pluck('id')
                        ->reject(function ($id) use ($page_item) {
                            return (int) $id === (int) $page_item->id;
                        })
                        ->implode(' ');
                @endphp
                <tr id="{{$page_item->id}}"
                    data-page-id="{{$page_item->id}}"
                    data-parent-id="{{$page_item->parent_id ?? ''}}"
                    data-depth="{{$page_item->depth}}"
                    data-ancestor-ids="{{$ancestor_ids}}"
                    data-has-children="{{$has_children ? 1 : 0}}">
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

                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="dropdownMenuButtonSequence" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButtonSequence">
                                <button type="button" class="dropdown-item" @if ($loop->first) disabled @endif onclick="javascript:submit_sequence_top({{$page_item->id}})">
                                    一番上へ
                                </button>
                                <button type="button" class="dropdown-item" @if ($loop->last) disabled @endif onclick="javascript:submit_sequence_bottom({{$page_item->id}})">
                                    一番下へ
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="table-text p-1" nowrap>
                        {{-- ページ移動 --}}
                        <a class="btn p-1 btn-primary btn-sm" id="move_level_{{$page_item->id}}" style="cursor:pointer;color:#FFF;" data-toggle="modal" data-target="#moveLevlModal" onclick='select_page({{$page_item->id}}, @json($page_item->page_name));' ><i class="fas fa-sitemap"></i></a>
                    </td>
                    <td class="table-text p-1 manage-page-pagename">
                        <div class="manage-page-tree" style="--cc-page-tree-depth: {{$page_item->depth}};">
                            <span class="manage-page-tree__indent" aria-hidden="true"></span>
                            @if ($has_children)
                                <button type="button"
                                    class="manage-page-tree__toggle"
                                    data-page-tree-toggle
                                    aria-expanded="true"
                                    aria-label="子ページを折り畳む"
                                    title="子ページを折り畳む">
                                    <i class="fas fa-chevron-down manage-page-tree__toggle-icon" aria-hidden="true"></i>
                                </button>
                            @else
                                <span class="manage-page-tree__toggle-placeholder" aria-hidden="true"></span>
                            @endif
                            <span class="manage-page-tree__label @if ($has_children) manage-page-tree__label--parent @endif">{{$page_item->page_name}}</span>
                        </div>
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
                            @php
                            $layout_inherit_flag = (string)($page_item->layout_inherit_flag ?? '1');
                            $layout_scope_label = ($layout_inherit_flag === '0') ? 'このページのみ' : '下層にも適用';
                            @endphp
                            <div>
                                <img src="{{asset('/images/core/layout/' . $page_item->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$page_item->getLayoutTitle()}}（{{$layout_scope_label}}）">
                                <div class="small text-muted">{{$layout_scope_label}}</div>
                            </div>
                        @else

                            @php
                            $layout_page_parent = new Page();
                            // 自分及び先祖ページを遡る
                            foreach ($page_tree as $page_tmp) {
                                if ($page_tmp->getSimpleLayout()) {
                                    if (!is_null($page_tmp->layout_inherit_flag) && (int)$page_tmp->layout_inherit_flag === 0) {
                                        continue;
                                    }
                                    $layout_page_parent = $page_tmp;
                                    break;
                                }
                            }
                            @endphp
                            @if ($layout_page_parent->getSimpleLayout())
                                <div class="border border-warning"><img src="{{asset('/images/core/layout/' . $layout_page_parent->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$layout_page_parent->getLayoutTitle()}}（親ページを継承）"></div>
                            @else
                                <div class="border border-info"><img src="{{asset('/images/core/layout/' . $base_layout_page->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$base_layout_page->getLayoutTitle()}}（基本レイアウト）"></div>
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
                        @if ($page_item->meta_robots)
                            @php
                            $meta_robots_descriptions = implode('、', PageMetaRobots::descriptions(explode(',', $page_item->meta_robots)));
                            $meta_robots_tooltip = $meta_robots_descriptions ?: $page_item->meta_robots;
                            @endphp
                            <div><i class="fas fa-robot" title="{{$meta_robots_tooltip}}"></i></div>
                        @else
                            @php
                            $meta_robots_parent = null;
                            foreach ($page_tree as $page_tmp) {
                                if ($page_tmp->meta_robots) {
                                    $meta_robots_parent = $page_tmp->meta_robots;
                                    break;
                                }
                            }
                            $meta_robots_parent_description = $meta_robots_parent ? implode('、', PageMetaRobots::descriptions(explode(',', $meta_robots_parent))) : '';
                            @endphp
                            @if ($meta_robots_parent)
                                <div><i class="fas fa-robot text-warning" title="{{$meta_robots_parent_description}}(親ページを継承)"></i></div>
                            @endif
                        @endif
                    </td>
                    <td class="table-text p-1 text-center">
                        <div>@if($page_item->class)<i class="fas fa-swatchbook" title="{{$page_item->class}}"></i>@endif</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
                </table>
                <small class="text-muted">※ 表示内容が多い場合、横スクロールできます。</small>
            </div><!-- /table-responsive -->
        </div>
    @endif
@endsection
