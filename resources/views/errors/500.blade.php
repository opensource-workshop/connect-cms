{{--
 * 500 例外エラー
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
@extends('layouts.app')
@section('content')
<div class="container">
    <div class="alert alert-danger mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <span class="sr-only">Error:</span>
        500 internal server error. （システムでエラーが発生しました）<br />

        @if (Config::get('app.debug'))
        <div class="card mt-3">
            <div class="card-header">
                Message detail.
            </div>
            <div class="card-body">
                <p class="card-text">{{$exception->getMessage()}}</p>
                <p class="card-text">{{$debug_message}}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
