
@include('common.errors_form_line')

@if ($errors->has('undelete'))
    <div class="alert alert-danger">
        <strong>{{ $errors->first('undelete') }}</strong>
    </div>
@endif

@if (isset($function) && $function == 'edit')
    <form class="form-horizontal" method="POST" action="{{url('/manage/user/update/')}}/{{$id}}">
@else
    <form class="form-horizontal" method="POST" action="{{route('register')}}">
@endif
    {{ csrf_field() }}

    @if (Auth::user() && Auth::user()->can('admin_user'))
        <div class="form-group row">
            <label for="name" class="col-md-4 col-form-label text-md-right">状態 <label class="badge badge-danger">必須</label></label>
            <div class="col-md-8 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('status', $user->status) == 0)
                        <input type="radio" value="0" id="status_0" name="status" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="status_0" name="status" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="status_0">利用可能</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('status', $user->status) == 1)
                        <input type="radio" value="1" id="status_1" name="status" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="status_1" name="status" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="status_1">利用不可</label>
                </div>
            </div>
        </div>
    @else
        {{-- 自動登録が許可されている場合の状態は利用可能とする --}}
        <input type="hidden" value="0" name="status">
    @endif

    <div class="form-group row">
        <label for="name" class="col-md-4 col-form-label text-md-right">ユーザ名 <label class="badge badge-danger">必須</label></label>

        <div class="col-md-8">
            <input id="name" type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" placeholder="表示されるユーザ名を入力します。" required autofocus>

            @if ($errors->has('name'))
                <div class="text-danger">{{ $errors->first('name') }}</div>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label for="userid" class="col-md-4 col-form-label text-md-right">ログインID <label class="badge badge-danger">必須</label></label>

        <div class="col-md-8">
            <input id="userid" type="text" class="form-control" name="userid" value="{{ old('userid', $user->userid) }}" placeholder="ログインするときのIDを入力します。" required autofocus>

            @if ($errors->has('userid'))
                <div class="text-danger">{{ $errors->first('userid') }}</div>
            @endif
        </div>
    </div>

    @if (Auth::user() && Auth::user()->can('admin_user'))
        {{-- 管理者によるユーザ登録 --}}
        <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">eメールアドレス</label>

            <div class="col-md-8">
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" placeholder="メールアドレスを入力します。">

                @if ($errors->has('email'))
                    <div class="text-danger">{{ $errors->first('email') }}</div>
                @endif
            </div>
        </div>
    @else
        {{-- 自動登録 --}}
        <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right">eメールアドレス <label class="badge badge-danger">必須</label></label>

            <div class="col-md-8">
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" placeholder="メールアドレスを入力します。" required autofocus>

                @if ($errors->has('email'))
                    <div class="text-danger">{{ $errors->first('email') }}</div>
                @endif
            </div>
        </div>
    @endif

    <div class="form-group row">
        @if (isset($function) && $function == 'edit')
            <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>
        @else
            <label for="password" class="col-md-4 col-form-label text-md-right">パスワード <label class="badge badge-danger">必須</label></label>
        @endif

        <div class="col-md-8">
            @if (isset($function) && $function == 'edit')
                <input id="password" type="password" class="form-control" name="password" autocomplete="new-password" placeholder="ログインするためのパスワードを入力します。">
            @else
                <input id="password" type="password" class="form-control" name="password" autocomplete="new-password" required placeholder="ログインするためのパスワードを入力します。">
            @endif

            @if ($errors->has('password'))
                @foreach ($errors->get('password') as $error)
                    <div class="text-danger">{{$error}}</div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="form-group row">
        @if (isset($function) && $function == 'edit')
            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">確認用パスワード</label>
        @else
            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">確認用パスワード <label class="badge badge-danger">必須</label></label>
        @endif

        <div class="col-md-8">
            @if (isset($function) && $function == 'edit')
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="パスワードと同じものを入力してください。">
            @else
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="パスワードと同じものを入力してください。">
            @endif
        </div>
    </div>

    {{-- ユーザーの追加カラム --}}
    @foreach($users_columns as $users_column)
        @php
            // ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない
            // 時間FromToは入力項目のtitleで項目説明しているため、項目名のラベルにforを付けない
            if ($users_column->column_type == UserColumnType::radio || $users_column->column_type == UserColumnType::checkbox || $users_column->column_type == UserColumnType::agree) {
                $label_for = '';
                $label_class = 'pt-0';
            } else {
                $label_for = 'for=user-column-' . $users_column->id;
                $label_class = '';
            }
        @endphp

        <div class="form-group row">
            <label class="col-md-4 col-form-label text-md-right {{$label_class}}" {{$label_for}}>{{$users_column->column_name}} @if ($users_column->required)<span class="badge badge-danger">必須</span> @endif</label>
            <div class="col-md-8">
                @include('auth.registe_form_input_' . $users_column->column_type, ['user_obj' => $users_column, 'label_id' => 'user-column-'.$users_column->id])
                <div class="small {{ $users_column->caption_color }}">{!! nl2br($users_column->caption) !!}</div>
            </div>
        </div>
    @endforeach

    {{-- 未ログイン（自動登録）時に個人情報保護方針への同意関係が設定されている場合 --}}
    @if (!Auth::user())
        @if (isset($configs['user_register_requre_privacy']) && $configs['user_register_requre_privacy'] == 1)
            <div class="form-group row">
                <label for="password-confirm" class="col-md-4 col-form-label text-md-right pt-0">個人情報保護方針への同意  <label class="badge badge-danger">必須</label></label>

                <div class="col-md-8">
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input name="user_register_requre_privacy" value="以下の内容に同意する。" type="checkbox" class="custom-control-input" id="user_register_requre_privacy">
                        <label class="custom-control-label" for="user_register_requre_privacy"> 以下の内容に同意する。</label>
                    </div>
                    @if ($errors->has('user_register_requre_privacy'))
                        <div class="text-danger">{{ $errors->first('user_register_requre_privacy') }}</div>
                    @endif
                    @if (isset($configs['user_register_privacy_description']))
                        {!!$configs['user_register_privacy_description']!!}
                    @endif
                </div>
            </div>
        @endif
    @endif

    {{-- コンテンツ権限 --}}
    @if (Auth::user() && Auth::user()->can('admin_user'))
        <div class="form-group row">
            <label for="password-confirm" class="col-md-4 text-md-right">コンテンツ権限</label>
            <div class="col-md-8">
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_article_admin"]) && $users_roles["base"]["role_article_admin"] == 1) ||
                          old('base.role_article_admin') == 1)
                        <input name="base[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin" checked="checked">
                    @else
                        <input name="base[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin">
                    @endif
                    <label class="custom-control-label" for="role_article_admin" id="label_role_article_admin">コンテンツ管理者</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_arrangement"]) && $users_roles["base"]["role_arrangement"] == 1) ||
                          old('base.role_arrangement') == 1)
                        <input name="base[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement" checked="checked">
                    @else
                        <input name="base[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement">
                    @endif
                    <label class="custom-control-label" for="role_arrangement" id="label_role_arrangement">プラグイン管理者</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_article"]) && $users_roles["base"]["role_article"] == 1) ||
                          old('base.role_article') == 1)
                        <input name="base[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article" checked="checked">
                    @else
                        <input name="base[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article">
                    @endif
                    <label class="custom-control-label" for="role_article" id="label_role_article">モデレータ（他ユーザの記事も更新）</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_approval"]) && $users_roles["base"]["role_approval"] == 1) ||
                          old('base.role_approval') == 1)
                        <input name="base[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval" checked="checked">
                    @else
                        <input name="base[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval">
                    @endif
                    <label class="custom-control-label" for="role_approval" id="label_role_approval">承認者</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_reporter"]) && $users_roles["base"]["role_reporter"] == 1) ||
                          old('base.role_reporter') == 1)
                        <input name="base[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter" checked="checked">
                    @else
                        <input name="base[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter">
                    @endif
                    <label class="custom-control-label" for="role_reporter" id="label_role_reporter">編集者</label>
                </div>
                <small class="text-muted">
                    ※「編集者」、「モデレータ」の記事投稿については、各プラグイン側の権限設定も必要です。<br>
                    ※「コンテンツ管理者」は、「コンテンツ管理者」権限と同時に「プラグイン管理者」「モデレータ」「承認者」「編集者」権限も併せて持ちます。
                </small>
            </div>
        </div>

        {{-- 管理権限 --}}
        <div class="form-group row">
            <label for="password-confirm" class="col-md-4 text-md-right">管理権限</label>
            <div class="col-md-8">
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_system"]) && $users_roles["manage"]["admin_system"] == 1) ||
                          old('manage.admin_system') == 1)
                        <input name="manage[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system" checked="checked">
                    @else
                        <input name="manage[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system">
                    @endif
                    <label class="custom-control-label" for="admin_system" id="label_admin_system">システム管理者</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_site"]) && $users_roles["manage"]["admin_site"] == 1) ||
                          old('manage.admin_site') == 1)
                        <input name="manage[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site" checked="checked">
                    @else
                        <input name="manage[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site">
                    @endif
                    <label class="custom-control-label" for="admin_site" id="label_admin_site">サイト管理者</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_page"]) && $users_roles["manage"]["admin_page"] == 1) ||
                          old('manage.admin_page') == 1)
                        <input name="manage[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page" checked="checked">
                    @else
                        <input name="manage[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page">
                    @endif
                    <label class="custom-control-label" for="admin_page" id="label_admin_page">ページ管理者</label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_user"]) && $users_roles["manage"]["admin_user"] == 1) ||
                          old('manage.admin_user') == 1)
                        <input name="manage[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user" checked="checked">
                    @else
                        <input name="manage[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user">
                    @endif
                    <label class="custom-control-label" for="admin_user" id="label_admin_user">ユーザ管理者</label>
                </div>
            </div>
        </div>
    @endif

    {{-- 役割設定 --}}
    @if (isset($original_role_configs))
    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 text-md-right">役割設定</label>
        <div class="col-md-6">
            @foreach($original_role_configs as $original_role_config)
            <div class="custom-control custom-checkbox">
                @if ((isset($original_role_config["role_value"]) && $original_role_config["role_value"] == 1) ||
                      old("original_role." . $original_role_config->name) == 1)
                    <input name="original_role[{{$original_role_config->name}}]" value="1" type="checkbox" class="custom-control-input" id="original_role{{$original_role_config->id}}" checked="checked">
                @else
                    <input name="original_role[{{$original_role_config->name}}]" value="1" type="checkbox" class="custom-control-input" id="original_role{{$original_role_config->id}}">
                @endif
                <label class="custom-control-label" for="original_role{{$original_role_config->id}}" id="label_original_role{{$original_role_config->id}}">{{$original_role_config->value}}</label>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            @if (Auth::user() && Auth::user()->can('admin_user'))
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user')}}'">
                <i class="fas fa-times"></i> キャンセル
            </button>
            @else
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}'">
                <i class="fas fa-times"></i> キャンセル
            </button>
            @endif
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 
                @if (isset($function) && $function == 'edit')
                    ユーザ変更
                @else
                    ユーザ登録
                @endif
            </button>
        </div>
        {{-- 既存ユーザの場合は削除処理のボタンも表示(自分自身の場合は表示しない) --}}
        @if (isset($id) && $id && $id != Auth::user()->id)
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">削除</span></span>
                </a>
            </div>
        @endif
    </div>
</form>

@if (isset($id) && $id && $id != Auth::user()->id)
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
