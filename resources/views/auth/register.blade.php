@extends('layouts.app')

@section('content')
<main class="container mt-3" role="main">

    @if(isset($configs["user_register_description"]))
    <div class="row mb-3">
        <div class="col-md-9 mx-auto">
            <div class="card">
                <div class="card-header bg-primary cc-primary-font-color">ユーザ登録について</div>
                <div class="card-body">
                    {!!$configs['user_register_description']!!}
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-9 mx-auto">
            <div class="card">
                <div class="card-header bg-primary cc-primary-font-color">ユーザ登録</div>
                <div class="card-body">
                    {{-- フォームをincude --}}
                    @include('auth.registe_form')
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
