{{--
 * グループ内ユーザリスト覧のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.group.group_manage_tab')
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>ユーザ名</th>
                    {{-- <th nowrap>グループ権限</th> --}}
                    <th nowrap>作成日</th>
                    <th nowrap>更新日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($group_users as $group_user)
                <tr>
                    <td>{{$group_user->user_name}}</td>
                    {{-- <td>{{GroupType::getDescription($group_user->group_role)}}</td> --}}
                    <td>{{$group_user->created_at->format('Y/m/d')}}</td>
                    <td>{{$group_user->updated_at->format('Y/m/d')}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        @if($group_users)
        <div class="text-center">
            {{$group_users->links()}}
        </div>
        @endif
    </div>
</div>

@endsection
