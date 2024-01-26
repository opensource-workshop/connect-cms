{{--
 * リセットパスワード
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category パスワードリセット
--}}
@extends('layouts.app')

@section('content')
<main class="container" role="main">
    <div class="row mt-3">
        <div class="col-12 mx-auto">
            <div class="card">
                <div class="card-header">{{-- Reset Password --}}リセット パスワード</div>

                <div class="card-body">
                    <form class="form-horizontal" method="POST" action="{{ route('password.request') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="row form-group">
                            <label for="email" class="col-md-3 col-form-label text-md-right">{{-- E-Mail Address --}}eメール</label>
                            <div class="col-md-7">
                                <input id="email" type="text" class="form-control @if ($errors->has('email')) border-danger @endif" name="email" value="{{ $email or old('email') }}" required autofocus>
                                @include('plugins.common.errors_inline', ['name' => 'email'])
                            </div>
                        </div>

                        <div class="row form-group">
                            <label for="password" class="col-md-3 col-form-label text-md-right">{{-- Password --}}新しいパスワード</label>
                            <div class="col-md-7">
                                <input id="password" type="password" class="form-control @if ($errors->has('password')) border-danger @endif" name="password" required>
                                @include('plugins.common.errors_inline', ['name' => 'password'])
                            </div>
                        </div>

                        <div class="row form-group">
                            <label for="password-confirm" class="col-md-3 col-form-label text-md-right">{{-- Confirm Password --}}新しいパスワードの確認</label>
                            <div class="col-md-7">
                                <input id="password-confirm" type="password" class="form-control @if ($errors->has('password_confirmation')) border-danger @endif" name="password_confirmation" required>
                                @include('plugins.common.errors_inline', ['name' => 'password_confirmation'])
                            </div>
                        </div>

                        <div class="row form-group">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> {{-- Reset Password --}}パスワードリセット
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
