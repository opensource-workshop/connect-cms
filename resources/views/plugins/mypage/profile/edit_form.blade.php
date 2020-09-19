{{--
 * copy by resources\views\auth\registe_form.blade.php
--}}

{{--
@include('common.errors_form_line')
--}}

@if ($errors->has('undelete'))
    <div class="alert alert-danger">
        <strong>{{ $errors->first('undelete') }}</strong>
    </div>
@endif

<form action="{{url('/')}}/mypage/profile/update/{{$id}}" class="form-horizontal" method="POST">
    {{ csrf_field() }}

    <div class="form-group row{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="col-md-4 col-form-label text-md-right">eメールアドレス</label>

        <div class="col-md-8">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" placeholder="メールアドレスを入力します。">

            @if ($errors->has('email'))
                <span class="text-danger">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">現在のパスワード</label>

        <div class="col-md-8">
            <input type="password" class="form-control" name="now_password" autocomplete="new-password" placeholder="現在のパスワードを入力します。">

            @if ($errors && $errors->has('now_password'))
                @foreach ($errors->get('now_password') as $error)
                <div class="text-danger">{{$error}}</div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">新しいパスワード</label>

        <div class="col-md-8">
            <input type="password" class="form-control" name="new_password" autocomplete="new-password" placeholder="新しいパスワードを入力します。">

            @if ($errors && $errors->has('new_password'))
                @foreach ($errors->get('new_password') as $error)
                <div class="text-danger">{{$error}}</div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">新しいパスワードの確認</label>

        <div class="col-md-8">
            <input type="password" class="form-control" name="new_password_confirmation" placeholder="新しいパスワードと同じものを入力します。">

            @if ($errors && $errors->has('new_password_confirmation'))
                @foreach ($errors->get('new_password_confirmation') as $error)
                <div class="text-danger">{{$error}}</div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 
                更新
            </button>
        </div>
    </div>
</form>

