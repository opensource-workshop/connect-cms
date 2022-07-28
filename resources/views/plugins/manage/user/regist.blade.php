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

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>

    {{-- ユーザ変更関連タブ --}}
    @include('plugins.manage.user.user_edit_tab')

    <div class="card-body">
        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- フォームをincude --}}
        @include('auth.registe_form')
    </div>
</div>

@endsection
