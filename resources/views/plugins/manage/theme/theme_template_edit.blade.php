{{--
 * テンプレート編集テンプレート
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

        <form action="{{url('/')}}/manage/theme/saveTemplate" method="POST">
            {{csrf_field()}}
            <input name="dir_name" type="hidden" value="{{$dir_name}}" />
            <textarea name="template" id="template" class="form-control" rows=20
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
            @include('plugins.common.codemirror', ['element_id' => 'template', 'mode' => 'javascript()', 'height' => '500px'])
            {{-- 注釈テキスト --}}
            <div class="text-muted small">
                テンプレートには、入力しておきたい文章やHTMLタグ（見出し・段落・リンクなど）をあらかじめ登録できます。<br>
                登録後は、エディタの「テンプレートの挿入」ボタンから呼び出せます。<br>
                下記の注意点も併せてご確認ください。<br><br>
                ＜注意点＞
            </div>
            <ul class="text-muted small">
                <li>複数行の文章を登録する場合は、改行タグ等を使ってください。
                    <ul class="mt-1">
                        <li>例1：<code>'&lt;p&gt;段落1&lt;/p&gt;&lt;p&gt;段落2&lt;/p&gt;'</code></li>
                        <li>例2：<code>'段落1&lt;br&gt;段落2'</code></li>
                        <li>例3：<code>`&lt;p&gt;段落1&lt;/p&gt;<br>&lt;p&gt;段落2&lt;/p&gt;`</code> ※改行を含む場合、引用符はバッククォートで囲みます。</li>
                    </ul>
                </li>
                <li><code>&lt;script&gt;</code> タグや危険なコードは登録しないでください。</li>
                <li>登録された内容はそのままエディタに挿入されます。表示崩れがないかご確認ください。</li>
            </ul>

            <p class="text-muted small mb-1">▼ テンプレート記述サンプル：</p>
            <pre class="bg-light p-2 rounded border text-dark small">
templates : [
    {
        title: 'サンプル1（&lt;p&gt;タグ）',
        description: '（解説）シングルクォートを使用しています。',
        content: '&lt;p&gt;１行サンプル&lt;/p&gt;&lt;p&gt;１行サンプル&lt;/p&gt;'
    },
    {
        title: 'サンプル2（改行）',
        description: '（解説）バッククォートを使用しています。',
        content: `&lt;p&gt;１行&lt;/p&gt;
&lt;p&gt;２行&lt;/p&gt;
&lt;p&gt;３行&lt;/p&gt;`
    },
    {
        title: 'サンプル3（&lt;img&gt;タグ）',
        description: '（解説）your_theme_directory_nameは、テーマのディレクトリ名に置き換えてください。',
        content: '&lt;img src="/themes/Users/your_theme_directory_name/images/sample.jpg"&gt;'
    },
]
            </pre>            
            {{-- ボタンエリア --}}
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
