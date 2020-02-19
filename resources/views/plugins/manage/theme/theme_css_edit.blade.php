{{--
 * CSS 編集テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card mb-3">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.theme.theme_manage_tab')
    </div>
    <div class="card-body">

        <form action="/manage/theme/saveCss" method="POST">
            {{csrf_field()}}
            <input name="dir_name" type="hidden" value="{{$dir_name}}" />
            <textarea name="css" class="form-control" rows=20>{{$css}}</textarea>

            <div class="form-group mt-3">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/theme/'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-primary form-horizontal">
                    <i class="fas fa-check"></i> CSS ファイル保存
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
