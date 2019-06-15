{{--
 * ユーザ登録画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.user.user_manage_tab')

<div class="panel panel-default">
<div class="panel-body">

    {{-- フォームをincude --}}
    @include('auth.registe_form')

</div>
</div>

@endsection
