@extends('layouts.app')

@section('content')
<main class="container" role="main">
    <div class="row mt-3">
        <div class="col-8 mx-auto">
            <div class="card">
                <div class="card-header">{{-- Reset Password --}}パスワード リセット</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label for="userid" class="col-md-3 col-form-label text-md-right">eメール</label>
                            <div class="col-md-7">
                                <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" required>
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> パスワードのリセットリンクを送信する。
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
