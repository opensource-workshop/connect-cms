
@include('common.errors_form_line')

@if (isset($function) && $function == 'edit')
    <form class="form-horizontal" method="POST" action="{{url('/manage/user/update/')}}/{{$id}}">
@else
    <form class="form-horizontal" method="POST" action="{{route('register')}}">
@endif
    {{ csrf_field() }}

    <div class="form-group row{{ $errors->has('name') ? ' has-error' : '' }}">
        <label for="name" class="col-md-4 col-form-label text-md-right">ユーザ名</label>

        <div class="col-md-8">
            <input id="name" type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" placeholder="表示されるユーザ名を入力します。" required autofocus>

            @if ($errors->has('name'))
                <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row{{ $errors->has('userid') ? ' has-error' : '' }}">
        <label for="userid" class="col-md-4 col-form-label text-md-right">ログインID</label>

        <div class="col-md-8">
            <input id="userid" type="text" class="form-control" name="userid" value="{{ old('userid', $user->userid) }}" placeholder="ログインするときのIDを入力します。" required autofocus>

            @if ($errors->has('userid'))
                <span class="help-block">
                    <strong>{{ $errors->first('userid') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="col-md-4 col-form-label text-md-right">eメールアドレス</label>

        <div class="col-md-8">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" placeholder="メールアドレスを入力します。">

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

        <div class="col-md-8">
            @if (isset($function) && $function == 'edit')
                <input id="password" type="password" class="form-control" name="password" placeholder="ログインするためのパスワードを入力します。">
            @else
                <input id="password" type="password" class="form-control" name="password" required placeholder="ログインするためのパスワードを入力します。">
            @endif

            @if ($errors->has('password'))
                <span class="help-block" style="margin-bottom: 0;">
                    @foreach ($errors->get('password') as $error)
                        <strong>{{$error}}</strong><br />
                    @endforeach

{{--                    <strong>{{ $errors->get('password') }}</strong> --}}
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 col-form-label text-md-right">確認用パスワード</label>

        <div class="col-md-8">
            @if (isset($function) && $function == 'edit')
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="パスワードと同じものを入力してください。">
            @else
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="パスワードと同じものを入力してください。">
            @endif
        </div>
    </div>


    {{-- 記事関連の権限 --}}
    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 text-md-right">記事関連の権限</label>
        <div class="col-md-6">
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["base"]) && isset($users_roles["base"]["role_article_admin"]) && $users_roles["base"]["role_article_admin"] == 1)
                    <input name="base[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin" checked="checked">
                @else
                    <input name="base[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin">
                @endif
                <label class="custom-control-label" for="role_article_admin">記事管理者</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["base"]) && isset($users_roles["base"]["role_arrangement"]) && $users_roles["base"]["role_arrangement"] == 1)
                    <input name="base[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement" checked="checked">
                @else
                    <input name="base[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement">
                @endif
                <label class="custom-control-label" for="role_arrangement">プラグイン配置</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["base"]) && isset($users_roles["base"]["role_reporter"]) && $users_roles["base"]["role_reporter"] == 1)
                    <input name="base[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter" checked="checked">
                @else
                    <input name="base[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter">
                @endif
                <label class="custom-control-label" for="role_reporter">記事追加</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["base"]) && isset($users_roles["base"]["role_approval"]) && $users_roles["base"]["role_approval"] == 1)
                    <input name="base[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval" checked="checked">
                @else
                    <input name="base[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval">
                @endif
                <label class="custom-control-label" for="role_approval">記事承認</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["base"]) && isset($users_roles["base"]["role_article"]) && $users_roles["base"]["role_article"] == 1)
                    <input name="base[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article" checked="checked">
                @else
                    <input name="base[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article">
                @endif
                <label class="custom-control-label" for="role_article">モデレータ（他ユーザの記事も更新）</label>
            </div>
        </div>
    </div>

    {{-- 管理権限 --}}
    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 text-md-right">管理権限</label>
        <div class="col-md-6">
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_system"]) && $users_roles["manage"]["admin_system"] == 1)
                    <input name="manage[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system" checked="checked">
                @else
                    <input name="manage[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system">
                @endif
                <label class="custom-control-label" for="admin_system">システム管理者</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_page"]) && $users_roles["manage"]["admin_page"] == 1)
                    <input name="manage[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page" checked="checked">
                @else
                    <input name="manage[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page">
                @endif
                <label class="custom-control-label" for="admin_page">ページ管理者</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_site"]) && $users_roles["manage"]["admin_site"] == 1)
                    <input name="manage[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site" checked="checked">
                @else
                    <input name="manage[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site">
                @endif
                <label class="custom-control-label" for="admin_site">サイト管理者</label>
            </div>
            <div class="custom-control custom-checkbox">
                @if(isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_user"]) && $users_roles["manage"]["admin_user"] == 1)
                    <input name="manage[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user" checked="checked">
                @else
                    <input name="manage[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user">
                @endif
                <label class="custom-control-label" for="admin_user">ユーザ管理者</label>
            </div>
        </div>
    </div>

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user')}}'">
                <i class="fas fa-times"></i> キャンセル
            </button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 
                @if (isset($function) && $function == 'edit')
                    ユーザ変更
                @else
                    ユーザ登録
                @endif
            </button>
        </div>
        {{-- 既存ユーザの場合は削除処理のボタンも表示 --}}
        @if (isset($id) && $id)
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">削除</span></span>
                </a>
            </div>
        @endif
    </div>
</form>

@if (isset($id) && $id)
<div id="collapse{{$id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">ユーザを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/manage/user/destroy/')}}/{{$id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('ユーザを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
