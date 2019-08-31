
@include('common.errors_form_line')

@if (isset($function) && $function == 'edit')
    <form class="form-horizontal" method="POST" action="{{url('/manage/user/update/')}}/{{$id}}">
@else
    <form class="form-horizontal" method="POST" action="{{route('register')}}">
@endif
    {{ csrf_field() }}

    <div class="form-group row{{ $errors->has('name') ? ' has-error' : '' }}">
        <label for="name" class="col-md-4 col-form-label text-md-right">ユーザ名</label>

        <div class="col-md-6">
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

        <div class="col-md-6">
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

        <div class="col-md-6">
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

        <div class="col-md-6">
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

        <div class="col-md-6">
            @if (isset($function) && $function == 'edit')
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="パスワードと同じものを入力してください。">
            @else
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="パスワードと同じものを入力してください。">
            @endif
        </div>
    </div>

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <button type="submit" class="btn btn-primary mr-3"><i class="fas fa-check"></i> 
                @if (isset($function) && $function == 'edit')
                    ユーザ変更
                @else
                    ユーザ登録
                @endif
            </button>
            <button type="button" class="btn btn-secondary" onclick="location.href='{{url('/manage/user')}}'">
                <i class="fas fa-times"></i> キャンセル
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
    <div class="panel panel-danger">
        <div class="panel-body">
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
