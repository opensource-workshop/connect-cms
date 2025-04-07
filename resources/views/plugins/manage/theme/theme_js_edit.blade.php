{{--
 * JavaScript 編集テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
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

        <form action="{{url('/')}}/manage/theme/saveJs" method="post">
            {{csrf_field()}}
            <input name="dir_name" type="hidden" value="{{$dir_name}}" />
            <textarea name="js" id="js" class="form-control" rows=20>{{$js}}</textarea>
            @include('plugins.common.codemirror', ['element_id' => 'js', 'mode' => 'javascript', 'height' => 500])

            <div class="form-group mt-3">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/theme/'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-primary form-horizontal">
                    <i class="fas fa-check"></i> JavaScript ファイル保存
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
