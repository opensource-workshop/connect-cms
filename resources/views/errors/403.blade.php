{{--
 * 403 認証エラー
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
@extends('layouts.app')
@section('content')
<div class="container">
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
        <span class="sr-only">Error:</span>
        403 Forbidden.<br />
        <p>　 {!!$exception->getMessage()!!}</p>
    </div>
</div>
@endsection
