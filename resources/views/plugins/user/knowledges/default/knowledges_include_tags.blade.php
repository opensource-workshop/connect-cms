{{--
 * 表示画面テンプレート。データのみ。HTMLは解釈する。
 * 
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ナレッジ・プラグイン
 --}}

<div class="card mt-3">
    <!-- Default panel contents -->
    <div class="card-header">タグで絞り込む</div>

    <div class="card-body pt-2">
        <label class="cc_label_panel_list">
            <div class="input-group">
                <div class="custom-control custom-checkbox mt-2">
                    <input name="search_tag1" value="1" type="checkbox" class="custom-control-input" id="search_tag1">
                    <label class="custom-control-label" for="search_tag1">ログイン</label>
                </div>
            </div>
        </label>
        <label class="cc_label_panel_list">
            <div class="input-group">
                <div class="custom-control custom-checkbox mt-2">
                    <input name="search_tag2" value="1" type="checkbox" class="custom-control-input" id="search_tag2">
                    <label class="custom-control-label" for="search_tag2">画像サイズ</label>
                </div>
            </div>
        </label>
        <label class="cc_label_panel_list">
            <div class="input-group">
                <div class="custom-control custom-checkbox mt-2">
                    <input name="search_tag3" value="1" type="checkbox" class="custom-control-input" id="search_tag3">
                    <label class="custom-control-label" for="search_tag3">ブログ</label>
                </div>
            </div>
        </label>
    </div>
</div>
