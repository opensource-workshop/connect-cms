{{--
 * ユーザ一覧のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">
        <div class="accordion" id="search_accordion">
            <div class="card">
                <button class="btn btn-link p-0 text-left collapsed" type="button" data-toggle="collapse" data-target="#search_collapse" aria-expanded="false" aria-controls="search_collapse">
                    <div class="card-header" id="user_search_condition">
                        絞り込み条件 <i class="fas fa-angle-down"></i>
                    </div>
                </button>
                @if (Session::has('user_search_condition'))
                <div id="search_collapse" class="collapse show" aria-labelledby="user_search_condition" data-parent="#search_accordion">
                @else
                <div id="search_collapse" class="collapse" aria-labelledby="user_search_condition" data-parent="#search_accordion">
                @endif
                    <div class="card-body">

                        <form name="form_search" id="form_search" class="form-horizontal" method="post" action="{{url('/')}}/manage/user/search">
                            {{ csrf_field() }}

                            {{-- ログインID --}}
                            <div class="form-group row">
                                <label for="user_search_condition_userid" class="col-md-3 col-form-label text-md-right">ログインID</label>
                                <div class="col-md-9">
                                    <input type="text" name="user_search_condition[userid]" id="user_search_condition_userid" value="{{Session::get('user_search_condition.userid')}}" class="form-control">
                                </div>
                            </div>

                            {{-- ユーザー名 --}}
                            <div class="form-group row">
                                <label for="user_search_condition_name" class="col-md-3 col-form-label text-md-right">ユーザー名</label>
                                <div class="col-md-9">
                                    <input type="text" name="user_search_condition[name]" id="user_search_condition_name" value="{{Session::get('user_search_condition.name')}}" class="form-control">
                                </div>
                            </div>

                            {{-- eメール --}}
                            <div class="form-group row">
                                <label for="user_search_condition_email" class="col-md-3 col-form-label text-md-right">eメール</label>
                                <div class="col-md-9">
                                    <input type="text" name="user_search_condition[email]" id="user_search_condition_email" value="{{Session::get('user_search_condition.email')}}" class="form-control">
                                </div>
                            </div>

                            @foreach($users_columns as $users_column)
                                @php
                                    // ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない
                                    if (UsersColumns::isChoicesColumnType($users_column->column_type)) {
                                        $label_for = '';
                                        $label_class = 'pt-0';
                                    } else {
                                        $label_for = 'for=user-column-' . $users_column->id;
                                        $label_class = '';
                                    }
                                @endphp

                                {{-- 通常の項目 --}}
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label text-md-right {{$label_class}}" {{$label_for}}>{{$users_column->column_name}}</label>
                                    <div class="col-md-9">
                                        @include('plugins.manage.user.list_search_' . $users_column->column_type, ['user_obj' => $users_column, 'label_id' => 'user-column-'.$users_column->id])
                                    </div>
                                </div>
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
                                </div>
                            </div>

                            {{-- ゲスト --}}
                            <div class="form-group row">
                                <label class="col-md-3 text-md-right">ゲスト</label>
                                <div class="col-md-9">
                                    <div class="custom-control custom-checkbox">
                                        <input name="user_search_condition[guest]" value="1" type="checkbox" class="custom-control-input" id="guest"@if(Session::get('user_search_condition.guest') == "1") checked @endif>
                                        <label class="custom-control-label" for="guest">ゲスト</label>
                                    </div>
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
                                        <option value="created_at_asc"@if(Session::get('user_search_condition.sort') == "created_at_asc" || !Session::has('user_search_condition.sort')) selected @endif>登録日時 昇順</option>
                                        <option value="created_at_desc"@if(Session::get('user_search_condition.sort') == "created_at_desc") selected @endif>登録日時 降順</option>
                                        <option value="updated_at_asc"@if(Session::get('user_search_condition.sort') == "updated_at_asc") selected @endif>更新日時 昇順</option>
                                        <option value="updated_at_desc"@if(Session::get('user_search_condition.sort') == "updated_at_desc") selected @endif>更新日時 降順</option>
                                        <option value="userid_asc"@if(Session::get('user_search_condition.sort') == "userid_asc") selected @endif>ログインID 昇順</option>
                                        <option value="userid_desc"@if(Session::get('user_search_condition.sort') == "userid_desc") selected @endif>ログインID 降順</option>
                                        @foreach($users_columns as $users_column)
                                            <option value="{{$users_column->id}}_asc" @if(Session::get('user_search_condition.sort') == $users_column->id . '_asc') selected @endif>{{  $users_column->column_name  }}(昇順)</option>
                                            <option value="{{$users_column->id}}_desc" @if(Session::get('user_search_condition.sort') == $users_column->id . '_desc') selected @endif>{{  $users_column->column_name  }}(降順)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- ボタンエリア --}}
                            <div class="form-group text-center">
                                <div class="row">
                                    <div class="mx-auto">
                                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user/clearSearch')}}'">
                                            <i class="fas fa-times"></i> クリア
                                        </button>
                                        <button type="submit" class="btn btn-primary form-horizontal">
                                            <i class="fas fa-check"></i> 絞り込み
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group table-responsive">
            <div class="text-right mt-3"><span class="badge badge-pill badge-light">{{ $users->total() }} 件</span></div>
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>ログインID</th>
                    <th nowrap>ユーザー名</th>
                    <th nowrap><i class="fas fa-users"></i> グループ</th>
                    <th nowrap>eメール</th>
                    @foreach($users_columns as $users_column)
                        <th nowrap>{{$users_column->column_name}}</th>
                    @endforeach
                    <th nowrap>権限</th>
                    <th nowrap>役割設定</th>
                    <th nowrap>状態</th>
                    <th nowrap>作成日</th>
                    <th nowrap>更新日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr class="{{$user->getStstusBackgroundClass()}}">
                    <td nowrap>
                        <a href="{{url('/')}}/manage/user/edit/{{$user->id}}" title="ユーザ変更"><i class="far fa-edit"></i></a>
                        {{$user->userid}}
                    </td>
                    <td>{{$user->name}}</td>
                    <td>
                        <a href="{{url('/')}}/manage/user/groups/{{$user->id}}" title="グループ参加"><i class="far fa-edit"></i></a>
                        @foreach($user->group_users as $group_user)
                            {{$group_user->name}}@if (!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>{{$user->email}}</td>
                    @foreach($users_columns as $users_column)
                        <td>@include('plugins.manage.user.list_include_value')</td>
                    @endforeach
                    <td nowrap>
                        @isset($user->view_user_roles)
                        <h6>
                            {!!$user->getRoleStringTag()!!}
{{--
                        @foreach($user->view_user_roles as $view_user_role)
                            @if ($view_user_role->role_name == 'role_article_admin')<span class="badge badge-danger">コ</span> @endif
                            @if ($view_user_role->role_name == 'role_arrangement')<span class="badge badge-primary">プ</span> @endif
                            @if ($view_user_role->role_name == 'role_article')<span class="badge badge-success">モ</span> @endif
                            @if ($view_user_role->role_name == 'role_approval')<span class="badge badge-warning">承</span> @endif
                            @if ($view_user_role->role_name == 'role_reporter')<span class="badge badge-info">編</span> @endif
                        @endforeach
--}}
                        </h6>
                        @endif
                    </td>
                    <td>
                        @isset($user->user_original_roles)
                            @foreach($user->user_original_roles as $user_original_role)
                                {{$user_original_role->value}}@if (!$loop->last), @endif
                            @endforeach
                        @endif
                    </td>
                    <td nowrap>{{UserStatus::getDescription($user->status)}}</td>
                    <td>{{$user->created_at->format('Y/m/d')}}</td>
                    <td>{{$user->updated_at->format('Y/m/d')}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        <div class="text-center">
            {{ $users->links() }}
        </div>
    </div>
</div>

@endsection
