{{--
 * 表示画面テンプレート。データのみ。HTMLは解釈する。
 * 
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ナレッジ・プラグイン
 --}}

<div class="card">
    <!-- Default panel contents -->
    <div class="card-header">ソフトウェア選択</div>

    <div class="card-body pt-2">
        <label class="cc_label_panel_list">
            <div class="input-group">
                <div class="custom-control custom-checkbox mt-2">
                    <input name="software_connect_cms" value="1" type="checkbox" class="custom-control-input" id="software_connect_cms">
                    <label class="custom-control-label" for="software_connect_cms">Connect-CMS</label>
                </div>
            </div>
        </label>
        <label class="cc_label_panel_list">
            <div class="input-group">
                <div class="custom-control custom-checkbox mt-2">
                    <input name="software_connect_netcommons3" value="1" type="checkbox" class="custom-control-input" id="software_connect_netcommons3">
                    <label class="custom-control-label" for="software_connect_netcommons3">NetCommons3</label>
                </div>
            </div>
        </label>
        <label class="cc_label_panel_list">
            <div class="input-group">
                <div class="custom-control custom-checkbox mt-2">
                    <input name="software_connect_netcommons2" value="1" type="checkbox" class="custom-control-input" id="software_connect_netcommons2">
                    <label class="custom-control-label" for="software_connect_netcommons2">NetCommons3</label>
                </div>
            </div>
        </label>
    </div>
</div>
