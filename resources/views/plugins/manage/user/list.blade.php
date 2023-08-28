{{--
 * ユーザ一覧のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
@php
use App\Models\Core\UsersColumns;
@endphp

{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- ダウンロード用フォーム --}}
<form method="post" name="user_download" action="{{url('/')}}/manage/user/downloadCsv/{{$columns_set_id}}">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_shift_jis() {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で現在の絞り込み条件のユーザをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        user_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        user_download.submit();
    }
    function submit_download_utf_8() {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で現在の絞り込み条件のユーザをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        user_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        user_download.submit();
    }

    $(function () {
        $('[data-toggle="popover"]').popover()
    })
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="accordion form-group" id="search_accordion">
            <div class="card">
                <button class="btn btn-link p-0 text-left collapsed" type="button" data-toggle="collapse" data-target="#search_collapse" aria-expanded="false" aria-controls="search_collapse">
                    <div class="card-header" id="user_search_condition">
                        絞り込み条件 <i class="fas fa-angle-down"></i>@if (Session::has('user_search_condition'))<span class="badge badge-pill badge-primary ml-2">条件設定中</span>@endif
                    </div>
                </button>
                {{-- @if (Session::has('user_search_condition'))
                <div id="search_collapse" class="collapse show" aria-labelledby="user_search_condition" data-parent="#search_accordion">
                @else --}}
                <div id="search_collapse" class="collapse" aria-labelledby="user_search_condition" data-parent="#search_accordion">
                {{-- @endif --}}
                    <div class="card-body border-bottom">

                        <form name="form_search" id="form_search" class="form-horizontal" method="post" action="{{url('/')}}/manage/user/search">
                            {{ csrf_field() }}

                            {{-- ログインID --}}
                            <div class="form-group row">
                                <label for="user_search_condition_userid" class="col-md-3 col-form-label text-md-right">{{ UsersColumns::getLabelLoginId($users_columns) }}</label>
                                <div class="col-md-9">
                                    <input type="text" name="user_search_condition[userid]" id="user_search_condition_userid" value="{{Session::get('user_search_condition.userid')}}" class="form-control">
                                </div>
                            </div>

                            {{-- ユーザ名 --}}
                            <div class="form-group row">
                                <label for="user_search_condition_name" class="col-md-3 col-form-label text-md-right">{{ UsersColumns::getLabelUserName($users_columns) }}</label>
                                <div class="col-md-9">
                                    <input type="text" name="user_search_condition[name]" id="user_search_condition_name" value="{{Session::get('user_search_condition.name')}}" class="form-control">
                                </div>
                            </div>

                            {{-- グループ --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right pt-0">グループ</label>
                                <div class="col-md-9">
                                    @php
                                        $values = (array) Session::get('user_search_condition.groups');
                                    @endphp
                                    <div class="container-fluid row">
                                        @forelse($groups_select as $group_select)
                                            @php
                                                // チェック用変数
                                                $column_checkbox_checked = "";

                                                // 入力されたデータの中に選択肢が含まれているか否か
                                                if (in_array($group_select->id, $values)) {
                                                    $column_checkbox_checked = " checked";
                                                }
                                            @endphp

                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input name="user_search_condition[groups][]" value="{{$group_select->id}}" type="checkbox" class="custom-control-input" id="user_search_condition[groups]_{{$loop->iteration}}"{{$column_checkbox_checked}}>
                                                <label class="custom-control-label" for="user_search_condition[groups]_{{$loop->iteration}}"> {{$group_select->name}}</label>
                                            </div>
                                        @empty
                                            グループなし
                                        @endforelse
                                    </div>
                                    <small class="form-text text-muted">※ 複数チェックを付けると、いずれかに該当する内容で絞り込みます。（OR検索）</small>
                                </div>
                            </div>

                            {{-- メールアドレス --}}
                            <div class="form-group row">
                                <label for="user_search_condition_email" class="col-md-3 col-form-label text-md-right">{{ UsersColumns::getLabelUserEmail($users_columns) }}</label>
                                <div class="col-md-9">
                                    <input type="text" name="user_search_condition[email]" id="user_search_condition_email" value="{{Session::get('user_search_condition.email')}}" class="form-control">
                                </div>
                            </div>

                            @foreach($users_columns as $column)
                                @if (UsersColumns::isLoopNotShowColumnType($column->column_type))
                                    {{-- 表示しない --}}
                                @else
                                    @php
                                        // ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない
                                        if (UsersColumns::isChoicesColumnType($column->column_type)) {
                                            $label_for = '';
                                            $label_class = 'pt-0';
                                        } else {
                                            $label_for = 'for=user-column-' . $column->id;
                                            $label_class = '';
                                        }
                                    @endphp

                                    {{-- 通常の項目 --}}
                                    <div class="form-group row">
                                        <label class="col-md-3 col-form-label text-md-right {{$label_class}}" {{$label_for}}>{{$column->column_name}}</label>
                                        <div class="col-md-9">
                                            @includeFirst(["plugins_option.manage.user.list_search_$column->column_type", "plugins.manage.user.list_search_$column->column_type"], ['user_obj' => $column, 'label_id' => 'user-column-'.$column->id])
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            {{-- コンテンツ権限 --}}
                            <div class="form-group row">
                                <label class="col-md-3 text-md-right">コンテンツ権限</label>
                                <div class="col-md-9">
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin"@if(Session::get('user_search_condition.role_article_admin') == "1") checked @endif>
                                        <label class="custom-control-label" for="role_article_admin">コンテンツ管理者</label><h6><span class="badge badge-danger ml-1">コ</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement"@if(Session::get('user_search_condition.role_arrangement') == "1") checked @endif>
                                        <label class="custom-control-label" for="role_arrangement">プラグイン管理者</label><h6><span class="badge badge-primary ml-1">プ</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article"@if(Session::get('user_search_condition.role_article') == "1") checked @endif>
                                        <label class="custom-control-label" for="role_article">モデレータ</label><h6><span class="badge badge-success ml-1">モ</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval"@if(Session::get('user_search_condition.role_approval') == "1") checked @endif>
                                        <label class="custom-control-label" for="role_approval">承認者</label><h6><span class="badge badge-warning ml-1">承</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter"@if(Session::get('user_search_condition.role_reporter') == "1") checked @endif>
                                        <label class="custom-control-label" for="role_reporter">編集者</label><h6><span class="badge badge-info ml-1">編</span></h6>
                                    </div>
                                    <small class="form-text text-muted">※ 「コンテンツ権限」「管理権限」「ゲスト」の中から複数チェックを付けると、いずれかに該当する内容で絞り込みます。（OR検索）</small>
                                </div>
                            </div>

                            {{-- 管理権限 --}}
                            <div class="form-group row">
                                <label class="col-md-3 text-md-right">管理権限</label>
                                <div class="col-md-9">
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system"@if(Session::get('user_search_condition.admin_system') == "1") checked @endif>
                                        <label class="custom-control-label" for="admin_system">システム管理者</label><h6><span class="badge badge-danger ml-1">シ</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site"@if(Session::get('user_search_condition.admin_site') == "1") checked @endif>
                                        <label class="custom-control-label" for="admin_site">サイト管理者</label><h6><span class="badge badge-primary ml-1">サ</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page"@if(Session::get('user_search_condition.admin_page') == "1") checked @endif>
                                        <label class="custom-control-label" for="admin_page">ページ管理者</label><h6><span class="badge badge-success ml-1">ペ</span></h6>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user"@if(Session::get('user_search_condition.admin_user') == "1") checked @endif>
                                        <label class="custom-control-label" for="admin_user">ユーザ管理者</label><h6><span class="badge badge-warning ml-1">ユ</span></h6>
                                    </div>
                                    <small class="form-text text-muted">※ 「コンテンツ権限」「管理権限」「ゲスト」の中から複数チェックを付けると、いずれかに該当する内容で絞り込みます。（OR検索）</small>
                                </div>
                            </div>

                            {{-- ゲスト --}}
                            <div class="form-group row">
                                <label class="col-md-3 text-md-right">ゲスト</label>
                                <div class="col-md-9">
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="user_search_condition[guest]" value="1" type="checkbox" class="custom-control-input" id="guest"@if(Session::get('user_search_condition.guest') == "1") checked @endif>
                                        <label class="custom-control-label" for="guest">ゲスト</label><h6><span class="badge badge-secondary ml-1">ゲ</span></h6>
                                    </div>
                                    <small class="form-text text-muted">
                                        ※ 「コンテンツ権限」「管理権限」「ゲスト」の中から複数チェックを付けると、いずれかに該当する内容で絞り込みます。（OR検索）<br />
                                        ※ 「ゲスト」とは、「コンテンツ権限」「管理権限」のいずれの権限もない状態です。<br />
                                    </small>
                                </div>
                            </div>

                            {{-- 状態 --}}
                            <div class="form-group row">
                                <label for="user_search_condition_status" class="col-md-3 col-form-label text-md-right">状態</label>
                                <div class="col-md">
                                    <select id="user_search_condition_status" name="user_search_condition[status]" class="form-control">
                                        <option value=""></option>
                                        @foreach (UserStatus::getMembers() as $enum_value => $enum_label)
                                            <div class="custom-control custom-radio custom-control-inline">
                                                @if (Session::get('user_search_condition.status') === (string)$enum_value)
                                                    <option value="{{$enum_value}}" selected>{{$enum_label}}</option>
                                                @else
                                                    <option value="{{$enum_value}}">{{$enum_label}}</option>
                                                @endif
                                            </div>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- 並べ替え --}}
                            <div class="form-group row">
                                <label for="sort" class="col-md-3 col-form-label text-md-right">並べ替え</label>
                                <div class="col-md-9">
                                    <select name="user_search_condition[sort]" id="sort" class="form-control">
                                        <option value="created_at_asc"@if(Session::get('user_search_condition.sort') == "created_at_asc" || !Session::has('user_search_condition.sort')) selected @endif>{{ UsersColumns::getLabelCreatedAt($users_columns) }} 昇順</option>
                                        <option value="created_at_desc"@if(Session::get('user_search_condition.sort') == "created_at_desc") selected @endif>{{ UsersColumns::getLabelCreatedAt($users_columns) }} 降順</option>
                                        <option value="updated_at_asc"@if(Session::get('user_search_condition.sort') == "updated_at_asc") selected @endif>{{ UsersColumns::getLabelUpdatedAt($users_columns) }} 昇順</option>
                                        <option value="updated_at_desc"@if(Session::get('user_search_condition.sort') == "updated_at_desc") selected @endif>{{ UsersColumns::getLabelUpdatedAt($users_columns) }} 降順</option>
                                        <option value="userid_asc"@if(Session::get('user_search_condition.sort') == "userid_asc") selected @endif>{{ UsersColumns::getLabelLoginId($users_columns) }} 昇順</option>
                                        <option value="userid_desc"@if(Session::get('user_search_condition.sort') == "userid_desc") selected @endif>{{ UsersColumns::getLabelLoginId($users_columns) }} 降順</option>
                                        @foreach($users_columns as $column)
                                            @if (UsersColumns::isLoopNotShowColumnType($column->column_type))
                                                {{-- 表示しない --}}
                                            @else
                                                <option value="{{$column->id}}_asc" @if(Session::get('user_search_condition.sort') == $column->id . '_asc') selected @endif>{{  $column->column_name  }}(昇順)</option>
                                                <option value="{{$column->id}}_desc" @if(Session::get('user_search_condition.sort') == $column->id . '_desc') selected @endif>{{  $column->column_name  }}(降順)</option>
                                            @endif
                                        @endforeach
                                        <option value="logged_in_at_asc"@if(Session::get('user_search_condition.sort') == "logged_in_at_asc") selected @endif>最終ログイン日時 昇順</option>
                                        <option value="logged_in_at_desc"@if(Session::get('user_search_condition.sort') == "logged_in_at_desc") selected @endif>最終ログイン日時 降順</option>
                                    </select>
                                </div>
                            </div>

                            {{-- ボタンエリア --}}
                            <div class="form-group text-center">
                                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user/clearSearch')}}'">
                                    <i class="fas fa-times"></i> クリア
                                </button>
                                <button type="submit" class="btn btn-primary form-horizontal">
                                    <i class="fas fa-check"></i> 絞り込み
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if (config('connect.USE_USERS_COLUMNS_SET'))
            {{-- 項目セット検索エリア --}}
            <div class="form-group">
                <button type="button" class="btn @if(empty($columns_set_id)) btn-primary @else btn-outline-primary @endif btn-sm" onclick="location.href='{{url('/')}}/manage/user/index?columns_set_id=0'">
                    ユーザ一覧(全て)
                </button>
                @foreach($columns_sets as $columns_set)
                    <button type="button" class="btn @if($columns_set->id == $columns_set_id) btn-primary @else btn-outline-primary @endif btn-sm" onclick="location.href='{{url('/')}}/manage/user/index?columns_set_id={{$columns_set->id}}'">
                        ユーザ一覧({{$columns_set->name}})
                    </button>
                @endforeach
            </div>
        @endif

        <div class="row">
            <div class="col-3 text-left d-flex align-items-end">
                {{-- (左側)件数 --}}
                <span class="badge badge-pill badge-light">{{ $users->total() }} 件</span>
            </div>

            @if ($columns_set_id)
                <div class="col text-right">
                    {{-- (右側)ダウンロードボタン --}}
                    <div class="btn-group">
                        <button type="button" class="btn btn-link" onclick="submit_download_shift_jis();">
                            <i class="fas fa-file-download"></i> ダウンロード
                        </button>
                        <button type="button" class="btn btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">ドロップダウンボタン</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" onclick="submit_download_shift_jis(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                            <a class="dropdown-item" href="#" onclick="submit_download_utf_8(); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                            <a class="dropdown-item" href="https://manual.connect-cms.jp/manage/user/index.html" target="_brank">
                                <span class="btn btn-link"><i class="fas fa-question-circle"></i> オンラインマニュアル</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="form-group table-responsive">
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>{{ UsersColumns::getLabelLoginId($users_columns) }}</th>
                    <th nowrap>{{ UsersColumns::getLabelUserName($users_columns) }}</th>
                    <th nowrap><i class="fas fa-users"></i> グループ</th>
                    <th nowrap>{{ UsersColumns::getLabelUserEmail($users_columns) }}</th>
                    @if (config('connect.USE_USERS_COLUMNS_SET'))
                        <th nowrap>項目セット</th>
                    @endif
                    @foreach($users_columns as $column)
                        @if (UsersColumns::isLoopNotShowColumnType($column->column_type))
                            {{-- 表示しない --}}
                        @else
                            <th nowrap>{{$column->column_name}}</th>
                        @endif
                    @endforeach
                    @if (empty($columns_set_id))
                        <th nowrap>項目セット値</th>
                    @endif
                    <th nowrap>権限</th>
                    <th nowrap>役割設定</th>
                    <th nowrap>状態</th>
                    <th nowrap>{{ UsersColumns::getLabelCreatedAt($users_columns) }}</th>
                    <th nowrap>{{ UsersColumns::getLabelUpdatedAt($users_columns) }}</th>
                    <th nowrap>最終ログイン日時</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr class="{{$user->getStstusBackgroundClass()}}">
                    <td nowrap>
                        @if ($user->hasRoleAdminSystem())
                            @if ($has_auth_role_admin_system)
                                {{-- システム管理者権限持ちユーザの編集は、システム管理者のみ可 --}}
                                <a href="{{url('/')}}/manage/user/edit/{{$user->id}}" title="ユーザ変更"><i class="far fa-edit"></i></a>
                            @else
                                {{-- 編集させない --}}
                            @endif
                        @else
                            {{-- 通常 --}}
                            <a href="{{url('/')}}/manage/user/edit/{{$user->id}}" title="ユーザ変更"><i class="far fa-edit"></i></a>
                        @endif
                        {{$user->userid}}
                    </td>
                    <td>{{$user->name}}</td>
                    <td nowrap>
                        {{-- <a href="{{url('/')}}/manage/user/groups/{{$user->id}}" title="グループ参加"><i class="far fa-edit"></i></a> --}}
                        {{-- {{$user->convertLoopValue('group_users', 'name')}} --}}
                        <a href="{{url('/')}}/manage/user/groups/{{$user->id}}" class="btn btn-success btn-sm" title="グループ参加">
                            <i class="fas fa-users"></i>
                        </a>

                        <button type="button" class="btn btn-outline-primary btn-sm" data-container="body" data-toggle="popover" data-placement="right"
                            title="参加グループ"
                            data-html="true"
                            data-content='
                            @foreach($user->group_users as $group_user)
                                <div class="small">{{$group_user->name}}</div>
                            @endforeach
                            '>
                            <span class="badge badge-light">{{count($user->group_users)}}</span>
                        </button>
                    </td>
                    <td>{{$user->email}}</td>
                    @if (config('connect.USE_USERS_COLUMNS_SET'))
                        <td>
                            {{ $user->columns_set_name }}
                            @if ($user->columns_set_name)
                                <a href="{{url('/')}}/manage/user/editColumns/{{$user->columns_set_id}}" class="badge badge-success"><i class="far fa-edit"></i> 項目</a>
                            @endif
                        </td>
                    @endif
                    @if (empty($columns_set_id))
                        <td>
                            {{ str_limit(strip_tags($user->inputs_column_value),100,'...') }}
                        </td>
                    @endif
                    @foreach($users_columns as $users_column)
                        @if (UsersColumns::isLoopNotShowColumnType($users_column->column_type))
                            {{-- 表示しない --}}
                        @else
                            <td>@includeFirst(['plugins_option.manage.user.list_include_value', 'plugins.manage.user.list_include_value'])</td>
                        @endif
                    @endforeach
                    <td nowrap>
                        <h6>{!!$user->getRoleStringTag()!!}</h6>
                    </td>
                    <td>
                        {{$user->convertLoopValue('user_original_roles', 'value')}}
                    </td>
                    <td nowrap>{{UserStatus::getDescription($user->status)}}</td>
                    <td>{{$user->created_at->format('Y/m/d H:i')}}</td>
                    <td>{{$user->updated_at->format('Y/m/d H:i')}}</td>
                    <td>
                        {{$user->max_logged_in_at ? (new Carbon($user->max_logged_in_at))->format('Y/m/d H:i') : ''}}
                        <a href="{{url('/')}}/manage/user/loginHistory/{{$user->id}}" title="ログイン履歴">履歴</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
            </table>
            <small class="text-muted">※ 横スクロールできます。</small>
        </div>

        {{-- ページング処理 --}}
        <div class="text-center">
            {{ $users->links() }}
        </div>
    </div>
</div>

@endsection
