@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2 mt-3">

            <div class="text-danger mb-3">
                <i class="fas fa-exclamation-circle"></i>
                このページの表示にはパスワードが必要です。
            </div>

            <div class="card">
                <div class="card-header">ページのパスワード認証</div>

                <div class="card-body">
                    <form class="" method="POST" action="{{url('/password/auth/')}}/{{$page_id}}">
                        {{ csrf_field() }}

                        <div class="form-group row{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block text-danger">
                                        <i class="fas fa-exclamation-circle"></i> {{$errors->first('password')}}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> ページ閲覧
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
