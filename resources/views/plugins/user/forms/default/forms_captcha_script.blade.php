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
    // mews/captchaライブラリのAPIエンドポイントから新しい画像URLを取得
    fetch('/captcha/api/flat?' + Math.random())
        .then(response => response.json())
        .then(data => {
            // 新しい画像URLで更新
            const captchaImg = document.querySelector('#captcha-{{$frame_id}}').parentElement.querySelector('img');
            if (captchaImg && data.img) {
                captchaImg.src = data.img;
            }
        })
        .catch(error => {
            console.error('Captcha refresh failed:', error);
            // フォールバック: 通常のエンドポイントで画像を取得
            const captchaImg = document.querySelector('#captcha-{{$frame_id}}').parentElement.querySelector('img');
            if (captchaImg) {
                captchaImg.src = '/captcha/flat?' + Math.random();
            }
        });
    
    // 入力フィールドをクリア
    const captchaInput = document.getElementById('captcha-{{$frame_id}}');
    if (captchaInput) {
        captchaInput.value = '';
        captchaInput.focus();
    }
}
</script>
@endif