{{--
 * Captcha入力フィールド共通テンプレート
 *
 * @author Claude
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}

{{-- Captcha フィールド（フォーム送信時認証の場合） --}}
@if ($form->access_limit_type == App\Enums\FormAccessLimitType::captcha_form_submit)
    <div class="form-group row">
        @if (isset($is_template_label_sm_4))
            <label class="col-sm-4 control-label" for="captcha-{{$frame_id}}">画像認証 <strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger lead' }}">{{__('messages.required')}}</strong></label>
        @elseif (isset($is_template_label_sm_6))
            <label class="col-sm-6 control-label" for="captcha-{{$frame_id}}">画像認証 <strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong></label>
        @elseif (isset($is_tandem_template))
            <label class="col-12 control-label" for="captcha-{{$frame_id}}">画像認証 <strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong></label>
        @else
            <label class="col-sm-2 control-label" for="captcha-{{$frame_id}}">画像認証 <strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong></label>
        @endif

        @if (isset($is_tandem_template))
            <div class="col-12">
        @else
            <div class="col-sm">
        @endif
            <div class="d-flex align-items-center mb-2">
                {!! captcha_img('flat') !!}
                <button type="button" class="btn btn-outline-secondary btn-sm ml-2" onclick="refreshCaptcha()" title="画像を更新">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <input type="text" id="captcha-{{$frame_id}}" name="captcha" class="form-control @if($errors && $errors->has('captcha')) is-invalid @endif" placeholder="画像に表示されている文字を入力してください" autocomplete="off" value="">
            @include('plugins.common.errors_inline', ['name' => 'captcha'])
            <div class="small text-muted mt-1">
                <i class="fas fa-info-circle"></i>
                文字が読みづらい場合は、右側の更新ボタンで新しい画像に変更できます。
            </div>
        </div>
    </div>
@endif
