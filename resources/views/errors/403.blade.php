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
    <div class="alert alert-danger mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <span class="sr-only">Error:</span>
        403 Forbidden. （権限がありません）<br />
{{--
        {{$exception->getMessage()}}
--}}
    </div>
</div>
@endsection
