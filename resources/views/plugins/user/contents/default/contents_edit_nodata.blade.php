{{--
 * 編集画面(データがなかった場合の)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- 機能選択タブ --}}
@include('plugins.user.contents.default.contents_edit_tab')

{{-- データ --}}
<p>
    <div class="panel panel-default">
        <div class="panel-body">
            コンテンツ・データが登録されていません。
        </div>
    </div>
</p>
