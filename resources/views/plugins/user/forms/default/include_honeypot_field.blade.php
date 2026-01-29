{{--
 * ハニーポットフィールド（スパムボット対策用の隠しフィールド）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
--}}
@if ($has_honeypot)
{{-- ボットが「画面上に存在する入力欄」と誤認しやすいよう、サイズを0にして透明にする --}}
<style>
.connect-hp-field {
    opacity: 0;
    position: absolute;
    top: 0;
    left: 0;
    height: 0;
    width: 0;
    z-index: -1;
}
</style>
<div class="connect-hp-field" aria-hidden="true">
    <label for="website_url">ウェブサイトURL（入力不要項目）</label>
    <input type="text" name="website_url" id="website_url" value="" autocomplete="off" tabindex="-1">
</div>
@endif
