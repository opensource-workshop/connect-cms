{{--
 * グループ登録画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category グループ管理
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
        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')

        <form class="form-horizontal" method="POST" action="{{ url('/manage/group/update/') }}@if($id)/{{$id}}@endif">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="name" class="col-md-3 col-form-label text-md-right">
                    グループ名
                    <span class="badge badge-danger">必須</span>
                </label>
                <div class="col-md-9">
                    <input id="name" type="text" class="form-control @if ($errors->has('name')) border-danger @endif" name="name" value="{{ old('name', $group->name) }}" placeholder="グループ名を入力します。" required autofocus>
                    @include('plugins.common.errors_inline', ['name' => 'name'])
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right"></label>
                <div class="col-md-9">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" value="" name="initial_group_flag">
                        <input name="initial_group_flag" value="1" type="checkbox" class="custom-control-input" id="initial_group_flag"
                            @if(old('name', $group->initial_group_flag) == "1") checked="checked" @endif
                        >
                        <label class="custom-control-label" for="initial_group_flag">初期参加グループ</label>
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'initial_group_flag'])
                    <small class="text-muted">※ 選択した場合、ユーザ登録時に参加させるグループになります。「ユーザ管理＞CSVインポート」は対象外です。</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="display_sequence" class="col-md-3 col-form-label text-md-right">表示順</label>
                <div class="col-md-9">
                    <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $group->display_sequence)}}" class="form-control @if ($errors->has('display_sequence')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'display_sequence'])
                    <small class="text-muted">※ 未指定時は最後に表示されるように自動登録します。</small>
                </div>
            </div>

            <div class="form-group row text-center">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/group')}}'">
                        <i class="fas fa-times"></i> キャンセル
                    </button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>
                        @if ($id)
                            グループ変更
                        @else
                            グループ登録
                        @endif
                    </button>
                </div>
                {{-- 既存グループの場合は削除処理のボタンも表示 --}}
                @if ($id)
                    <div class="col-sm-3 pull-right text-right">
                        <a data-toggle="collapse" href="#collapse{{$id}}">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="d-none d-sm-inline">削除</span></span>
                        </a>
                    </div>
                @endif
            </div>
        </form>

    </div>
</div>

@if ($id)
    <div id="collapse{{$id}}" class="collapse" style="margin-top: 8px;">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">グループを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/manage/group/delete/')}}/{{$id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('グループを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($id)
    {{-- ユーザーのグループ脱退 --}}
    <form id="remove-user" name="removeuser" method="POST" action="{{url('/manage/group/removeUser/')}}/{{$id}}">
        {{csrf_field()}}
        <input type="hidden" id="user_id" name="user_id" value="">
    </form>
    <script>
        function removeUser(user_id, user_name) {
            if (window.confirm(user_name + "がグループ不参加になります。よろしいですか？")) {
                document.getElementById("user_id").value = user_id;
                document.removeuser.action = document.removeuser.action;
                document.removeuser.submit();
            }
        }
    </script>

    {{-- ユーザーのグループ参加 --}}
    <form id="join-user" name="joinuser" method="POST" action="{{url('/manage/group/joinUser/')}}/{{$id}}">
        {{csrf_field()}}
        <input type="hidden" id="user_id" name="user_id" value="">
    </form>

    <div class="card mt-3" id="list">
        <div class="card-header">ユーザ参加</div>
        <div class="card-body">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <button class="btn btn-primary" type="button" v-on:click="getUsers()">ユーザ検索</button>
                </div>
                <input type="text" class="form-control" placeholder="ユーザ名で検索できます" v-model="keyword">
            </div>
            <ul class="list-group" id="users">
                <li class="list-group-item" v-for="user in users">
                    <button class="btn btn-primary btn-sm mr-1" v-on:click="joinUser(user.id, user.name)">参加</button>
                    <span>@{{ user.name }}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">【{{$group->name}}】グループ参加ユーザ一覧</div>
        <div class="card-body">
            {{-- 登録後メッセージ表示 --}}
            @include('plugins.common.flash_message')

            <div class="table-responsive">
                <table class="table table-hover cc-font-90">
                    <thead>
                        <tr>
                            <th nowrap>ユーザ名</th>
                            {{-- <th nowrap>グループ権限</th> --}}
                            <th nowrap>作成日</th>
                            <th nowrap>更新日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group_users as $group_user)
                            <tr>
                                <td>{{$group_user->user_name}}</td>
                                {{-- <td>{{GroupType::getDescription($group_user->group_role)}}</td> --}}
                                <td>{{ optional($group_user->created_at)->format('Y/m/d') }}</td>
                                <td>{{ optional($group_user->updated_at)->format('Y/m/d') }}</td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeUser({{$group_user->user_id}}, '{{$group_user->user_name}}');">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
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

    <script>
        createApp({
            data: function() {
                return {
                    keyword: '',
                    users: [],
                }
            },
            methods: {
                // ユーザーを取得する
                getUsers: function() {
                    let self = this;

                    if (self.keyword === '') {
                        self.users = [];
                        return;
                    }

                    //  非同期通信でユーザ取得
                    axios.get(
                            "{{ url('/') }}/manage/group/notJoinedUsers?user_name=" + self.keyword + '&group_id=' + {{$id}})
                        .then(function(res) {
                            self.users = res.data;
                        })
                        .catch(function(error) {
                            console.log(error)
                        });
                },
                // ユーザーをグループ参加させる
                joinUser: function(user_id, user_name) {
                    if (window.confirm(user_name + "がグループに参加します。よろしいですか？")) {
                        document.getElementById("user_id").value = user_id;
                        document.removeuser.action = document.joinuser.action;
                        document.removeuser.submit();
                    }
                }
            }
        }).mount('#list');
    </script>
@endif
@endsection
