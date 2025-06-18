{{--
 * Captcha JavaScript機能共通テンプレート
 *
 * @author Claude
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}

@if ($form->access_limit_type == App\Enums\FormAccessLimitType::captcha_form_submit)
<script>
function refreshCaptcha() {
    // 入力フィールドをクリア
    const captchaInput = document.getElementById('captcha-{{$frame_id}}');
    if (captchaInput) {
        captchaInput.value = '';
    }

    // 新しいCaptcha画像を取得
    const captchaImg = document.querySelector('#captcha-{{$frame_id}}').parentElement.querySelector('img');
    if (captchaImg) {
        // キャッシュバスターを使用して新しい画像を取得
        const newSrc = '{{url('/')}}/captcha/flat?' + Date.now();
        captchaImg.src = newSrc;

        // 画像読み込み完了後にフォーカス
        captchaImg.onload = function() {
            if (captchaInput) {
                captchaInput.focus();
            }
        };
    }
}
</script>
@endif
