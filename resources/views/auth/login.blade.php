@extends('layouts.app')

@section('content')
<main class="container" role="main">
    <div class="row">
        <div class="col-md-8 offset-md-2 mt-3">
            <div class="card">
                <div class="card-header">ログイン</div>

                <div class="card-body">
                    <form class="" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group row{{ $errors->has('userid') ? ' has-error' : '' }}">
                            <label for="userid" class="col-md-4 col-form-label text-md-right">ログインID</label>

                            <div class="col-md-6">
                                <input id="userid" type="text" class="form-control" name="userid" value="{{ old('userid') }}" required autofocus>

                                @if ($errors->has('userid'))
                                    <span class="help-block text-danger">
                                        <strong>{{ $errors->first('userid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="custom-control custom-checkbox mr-sm-2">
                                    <input type="checkbox" name="remember"{{old('remember') ? 'checked' : ''}} class="custom-control-input" id="remember">
                                    <label class="custom-control-label" for="remember" title="ログイン状態を維持するチェックボックス">ログイン状態を維持する。</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary" dusk="login-button">
                                    <i class="fas fa-check"></i> ログイン
                                </button>

                                @php
                                    use App\Models\Core\Configs;

                                    // パスワードリセットの使用
                                    $base_login_password_reset = Configs::where('name', 'base_login_password_reset')->first();
                                @endphp
                                @if (isset($base_login_password_reset) && $base_login_password_reset->value == '1')
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        パスワードを忘れた場合。
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
