{{--
 * テンプレート編集テンプレート
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

        <form action="{{url('/')}}/manage/theme/saveTemplate" method="POST">
            {{csrf_field()}}
            <input name="dir_name" type="hidden" value="{{$dir_name}}" />
            <textarea name="template" class="form-control" rows=20
                placeholder="（例）&#13;
templates : [&#13;
    {&#13;
        title: '１行サンプル',&#13;
        description: '１行サンプルの説明',&#13;
        content: '<p>１行サンプル</p><p>１行サンプル</p>'&#13;
    },&#13;
    {&#13;
        title: '複数行サンプル',&#13;
        description: '複数行サンプルの説明',&#13;
        content: `&#13;
<p>１行</p>&#13;
<p>２行</p>&#13;
<p>３行</p>`&#13;
    }&#13;
],">{{$template}}</textarea>
            <div class="form-group mt-3">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/theme/'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-primary form-horizontal">
                    <i class="fas fa-check"></i> テンプレートファイル保存
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
