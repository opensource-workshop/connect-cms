{{--
 * Page 編集画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分への挿入 --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.page.page_manage_tab')
    </div>

    {{-- ページ変更関連タブ --}}
    @include('plugins.manage.page.page_edit_tab')

    <div class="card-body">

        {{-- 編集画面(入力フォーム) --}}
        @include('plugins.manage.page.page_form')

        {{-- 削除画面(入力フォーム) --}}
    </div>
</div>

{{-- 編集モード＆ページがトップページではない --}}
@if ($page->id && $pages && $pages->first()->id != $page->id)
<div id="collapse{{$page->id}}" class="collapse mt-3">
    <div class="card border-danger">
        <div class="card-body">
            @if ($has_children)
                <p class="text-danger">子ページがあるため削除できません。子ページを最上位または別の親ページへ移動してから、削除してください。</p>
                <a href="{{ url('/manage/page') }}">ページ一覧へ</a>
            @else
                <span class="text-danger">ページを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/manage/page/destroy')}}/{{$page->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

@endsection
