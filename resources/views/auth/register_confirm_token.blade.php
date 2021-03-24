{{--
 * トークンを使った本登録の確定画面テンプレート。
--}}
@extends('layouts.app')

@section('content')
<main class="container mt-3" role="main">
    <form action="{{url('/')}}/register/storeToken/{{$id}}/{{$token}}" name="register_add_column" method="POST" class="form-horizontal">
        {{ csrf_field() }}

        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i> ユーザ本登録ボタンを１度だけ押して登録を確定してください。
            <br>
            <i class="fas fa-exclamation-circle"></i> 本登録処理完了までと本登録完了メール到着までは、時間を要する場合があります。
            <br>
            <i class="fas fa-exclamation-circle"></i> 本登録完了後に再度、仮登録メールよりユーザ本登録は行わないでください。
        </div>

        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('ユーザを本登録します。よろしいですか？')">
            <i class="fas fa-check"></i> ユーザ本登録
            </button>
        </div>
    </form>
</main>
@endsection
