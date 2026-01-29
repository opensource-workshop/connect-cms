{{--
 * ハニーポットフィールド（スパムボット対策用の隠しフィールド）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 * @note CSSは public/css/connect.css の .connect-hp-field を参照
--}}
@if ($has_honeypot)
<div class="connect-hp-field" aria-hidden="true">
    <label for="website_url">ウェブサイトURL（入力不要項目）</label>
    <input type="text" name="website_url" id="website_url" value="" autocomplete="off" tabindex="-1">
</div>
@endif
