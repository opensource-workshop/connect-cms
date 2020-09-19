{{--
 * ユーザ編集画面のテンプレート
--}}
{{-- マイページ画面ベース画面 --}}
@extends('plugins.mypage.mypage')

{{-- マイページ画面メイン部分のコンテンツ section:mypage_content で作ること --}}
@section('mypage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.mypage.profile.profile_mypage_tab')

</div>
<div class="card-body">

    {{-- フォームをincude --}}
    @include('plugins.mypage.profile.edit_form')

</div>
</div>

@endsection
