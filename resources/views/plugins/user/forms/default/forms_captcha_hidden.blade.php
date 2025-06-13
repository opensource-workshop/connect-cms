{{--
 * Captcha隠しフィールド共通テンプレート（確認画面用）
 *
 * @author Claude
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}

{{-- フォーム送信時Captcha認証の場合、Captcha値を保持 --}}
@if ($form->access_limit_type == App\Enums\FormAccessLimitType::captcha_form_submit)
    <input type="hidden" name="captcha" value="{{ old('captcha') }}">
@endif