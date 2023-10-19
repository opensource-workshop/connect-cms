{{--
 * メール設定 例外エラー
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
@extends('layouts.app')
@section('content')
<main class="container" role="main">
    <div class="alert alert-danger mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <span class="sr-only">Error:</span>
        メール設定エラーのため、メールを送信できませんでした。システム管理＞メール設定を見直してください。（500 internal server error.）<br />
        {{$exception->getMessage()}}
    </div>
</main>
@endsection
