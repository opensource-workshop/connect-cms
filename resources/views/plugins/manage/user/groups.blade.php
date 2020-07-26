{{--
 * ユーザのグループ参加一覧のテンプレート
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
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i> ユーザのグループに対する参加を設定します。
{{--
            <i class="fas fa-exclamation-circle"></i> ユーザのグループに対する権限を設定します。
--}}
        </div>

        <form action="{{url('/')}}/manage/user/saveGroups/{{$user->id}}" method="POST" class="">
            {{ csrf_field() }}
            <div class="form-group table-responsive">
                <table class="table table-hover cc-font-90">
                <thead>
                    <tr>
                        <th nowrap>グループ名</th>
                        <th nowrap>参加</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($group_users as $group_user)
                    <tr>
                        <td>{{$group_user->name}}</td>
                        <td>
                            <div class="custom-control custom-radio custom-control-inline">
                                @if(empty($group_user->group_role))
                                    <input type="radio" value="" id="group_role_{{$group_user->id}}_0" name="group_roles[{{$group_user->id}}]" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="" id="group_role_{{$group_user->id}}_0" name="group_roles[{{$group_user->id}}]" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="group_role_{{$group_user->id}}_0">不参加</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                @if($group_user->group_role == "general")
                                    <input type="radio" value="general" id="group_role_{{$group_user->id}}_1" name="group_roles[{{$group_user->id}}]" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="general" id="group_role_{{$group_user->id}}_1" name="group_roles[{{$group_user->id}}]" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="group_role_{{$group_user->id}}_1">参加</label>
{{--
                                <label class="custom-control-label" for="group_role_{{$group_user->id}}_1">一般</label>
--}}
                            </div>
{{-- グループに対する設定を権限で詳細化する際に使用。
                            <div class="custom-control custom-radio custom-control-inline">
                                @if($group_user->group_role == "moderator")
                                    <input type="radio" value="moderator" id="group_role_{{$group_user->id}}_4" name="group_roles[{{$group_user->id}}]" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="moderator" id="group_role_{{$group_user->id}}_4" name="group_roles[{{$group_user->id}}]" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="group_role_{{$group_user->id}}_4">モデレータ</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                @if($group_user->group_role == "manager")
                                    <input type="radio" value="manager" id="group_role_{{$group_user->id}}_3" name="group_roles[{{$group_user->id}}]" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="manager" id="group_role_{{$group_user->id}}_3" name="group_roles[{{$group_user->id}}]" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="group_role_{{$group_user->id}}_3">管理者</label>
                            </div>
--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                </table>
            </div>

            <div class="form-group text-center">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
            </div>
        </form>

        {{-- ページング処理 --}}
        <div class="text-center">
            {{ $group_users->links() }}
        </div>
    </div>
</div>

@endsection
