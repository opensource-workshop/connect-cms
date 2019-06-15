
@include('common.errors')

@if (isset($function) && $function == 'edit')
    <form class="form-horizontal" method="POST" action="{{url('/manage/user/update/')}}/{{$id}}">
@else
    <form class="form-horizontal" method="POST" action="{{route('register')}}">
@endif
    {{ csrf_field() }}

    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
        <label for="name" class="col-md-4 control-label">ユーザ名</label>

        <div class="col-md-6">
            <input id="name" type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" placeholder="表示されるユーザ名を入力します。" required autofocus>

            @if ($errors->has('name'))
                <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('userid') ? ' has-error' : '' }}">
        <label for="userid" class="col-md-4 control-label">ログインID</label>

        <div class="col-md-6">
            <input id="userid" type="text" class="form-control" name="userid" value="{{ old('userid', $user->userid) }}" placeholder="ログインするときのIDを入力します。" required autofocus>

            @if ($errors->has('userid'))
                <span class="help-block">
                    <strong>{{ $errors->first('userid') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="col-md-4 control-label">eメールアドレス</label>

        <div class="col-md-6">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" placeholder="メールアドレスを入力します。">

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password" class="col-md-4 control-label">パスワード</label>

        <div class="col-md-6">
            @if (isset($function) && $function == 'edit')
                <input id="password" type="password" class="form-control" name="password" placeholder="ログインするためのパスワードを入力します。">
            @else
                <input id="password" type="password" class="form-control" name="password" required placeholder="ログインするためのパスワードを入力します。">
            @endif

            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group">
        <label for="password-confirm" class="col-md-4 control-label">確認用パスワード</label>

        <div class="col-md-6">
            @if (isset($function) && $function == 'edit')
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="パスワードと同じものを入力してください。">
            @else
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="パスワードと同じものを入力してください。">
            @endif
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-md-offset-4">
            <button type="submit" class="btn btn-primary">
                @if (isset($function) && $function == 'edit')
                    ユーザ変更
                @else
                    ユーザ登録
                @endif
            </button>
        </div>
    </div>
</form>
