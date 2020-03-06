@extends('layouts.app')

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-md-9 mx-auto">
            <div class="card">
                <div class="card-header">ユーザ登録</div>
                <div class="card-body">
                    {{-- フォームをincude --}}
                    @include('auth.registe_form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
