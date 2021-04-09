{{--
 * プラグイン管理－カテゴリ設定画面の共通blade
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
<div class="card card-body bg-light p-2 mb-3">
    <ul>
        <li>サイト全体での共通カテゴリは<a href="{{ url('/manage/site/categories') }}" target="_blank">管理画面</a>から設定することができます。</li>
        <li>共通カテゴリは表示設定、及び、表示順のみ各プラグイン側で設定することが可能です。</li>
        <li>共通カテゴリ以外にも、本画面にてプラグイン単位の個別カテゴリを設定することが可能です。</li>
        <li>「文字色」「背景色」にはHTMLで指定できる色キーワード（例：<code>red</code>, <code>blue</code>）やRGB色（例：<code>#000000</code>, <code>#111</code>）等を設定できます。</li>
        <li>「クラス名」はCSSのクラス名を設定できます。<code>cc_category_クラス名</code> で使用できます。</li>
    </ul>
</div>
