{{--
 * パスワードリセット
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
                <div class="card-header">{{-- Reset Password --}}パスワード リセット</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                        <input type="hidden" name="_token" value="{{csrf_token()}}" dusk="token">

                        <div class="form-group row">
                            <label for="userid" class="col-md-3 col-form-label text-md-right">eメール</label>
                            <div class="col-md-7">
                                <input id="email" type="text" class="form-control @if ($errors->has('email')) border-danger @endif" name="email" value="{{ old('email') }}" required>
                                @include('plugins.common.errors_inline', ['name' => 'email'])
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-9 offset-md-3">
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
