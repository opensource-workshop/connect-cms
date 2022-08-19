{{--
 * ページ権限設定
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
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
        <div class="table-responsive">
            <table class="table table-striped table-bordered cc-font-90">
                <thead>
                    <tr>
                        <th nowrap rowspan="2">ページ名</th>
                        <th nowrap rowspan="2" class="pl-1"><i class="fas fa-key" title="閲覧パスワードあり"></i></th>
                        <th nowrap rowspan="2" class="pl-1"><i class="fas fa-lock" title="メンバーシップページ・ログインユーザ全員参加"></i></th>
                        <th nowrap rowspan="2" class="text-center"><i class="fas fa-users" title="ページ権限設定"></i></th>
                        <th colspan="{{$groups->count()}}" class="py-1">グループ名</th>
                    </tr>
                    <tr>
                        @foreach($groups as $group)
                            <th style="min-width: 100px;" class="py-1">{{$group->name}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $page_item)
                        <tr id="{{$page_item->id}}">
                            @php
                            // 自分のページから親を遡って取得（＋トップページ）
                            $page_tree = $page_item->getPageTreeByGoingBackParent(null);
                            @endphp

                            <td class="table-text p-1 manage-page-pagename">
                                <a href="{{url('/manage/page/edit')}}/{{$page_item->id}}" target="_blank"><i class="far fa-edit"></i></a>
                                {{-- 各ページの深さをもとにインデントの表現 --}}
                                @for ($i = 0; $i < $page_item->depth; $i++)
                                    @if ($i+1==$page_item->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                                @endfor
                                {{$page_item->page_name}}
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
                                        <a href="{{url('/manage/page/role')}}/{{$page_item->id}}" class="btn btn-outline-success btn-sm" target="_blank">
                                            <i class="fas fa-users" title="ページ権限設定"></i> <span class="badge badge-light">権限なし</span> <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @else
                                        <a href="{{url('/manage/page/role')}}/{{$page_item->id}}" class="btn btn-outline-warning btn-sm" target="_blank">
                                            <i class="fas fa-users" title="ページ権限設定"></i> <span class="badge badge-light">親を継承</span> <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif

                                @else
                                    <a href="{{url('/manage/page/role')}}/{{$page_item->id}}" class="btn btn-success btn-sm" target="_blank">
                                        <i class="fas fa-users" title="ページ権限設定"></i> <span class="badge badge-light">権限あり</span> <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </td>

                            @foreach($groups as $group)
                                <td class="table-text p-1 text-center">
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
                                            {{-- 権限なし --}}
                                        @else
                                            @php $group->page_roles = $page_roles_parent->where('group_id', $group->id); @endphp
                                            @foreach($group->getRoleNames() as $role_name)
                                                <span class="badge badge-warning" title="{{$role_name}}(親を継承)">{{$role_name}}</span>
                                            @endforeach
                                        @endif
                                    @else
                                        @php $group->page_roles = $page_item->page_roles->where('group_id', $group->id); @endphp
                                        @foreach($group->getRoleNames() as $role_name)
                                            <span class="badge badge-primary">{{$role_name}}</span>
                                        @endforeach
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    <tr>
                        <th colspan="4">グループ名</th>
                        @foreach($groups as $group)
                            <th class="py-1">{{$group->name}} <a href="{{url('/manage/group/edit')}}/{{$group->id}}" target="_blank"> <i class="fas fa-external-link-alt"></i></a></th>
                        @endforeach
                    </tr>
                    <tr>
                        <th colspan="4">参加ユーザ</th>
                        @foreach($groups as $group)
                            <td nowrap class="table-text py-1"><small>{!!$group->group_user_names!!}</small></td>
                        @endforeach
                    </tr>

                </tbody>
            </table>
            <small class="text-muted">※ 横スクロールできます。</small>
        </div>
    @endif
@endsection
