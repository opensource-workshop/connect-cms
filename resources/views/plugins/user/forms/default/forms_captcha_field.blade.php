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
        @php
            $label_class = 'col-sm-2';
            $required_class = 'text-danger';
            
            if (isset($is_template_label_sm_4) && $is_template_label_sm_4) {
                $label_class = 'col-sm-4';
                $required_class = App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger lead';
            } elseif (isset($is_template_label_sm_6) && $is_template_label_sm_6) {
                $label_class = 'col-sm-6';
                $required_class = App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger';
            } elseif (isset($is_tandem_template) && $is_tandem_template) {
                $label_class = 'col-12';
                $required_class = App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger';
            } else {
                $required_class = App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger';
            }
        @endphp
        <label class="{{$label_class}} control-label" for="captcha-{{$frame_id}}">{{__('messages.image_authentication')}} <strong class="{{$required_class}}">{{__('messages.required')}}</strong></label>

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
            <input type="text" id="captcha-{{$frame_id}}" name="captcha" class="form-control @if($errors && $errors->has('captcha')) is-invalid @endif" placeholder="{{__('messages.captcha_placeholder')}}" autocomplete="off" value="">
            @include('plugins.common.errors_inline', ['name' => 'captcha'])
            <div class="small text-muted mt-1">
                <i class="fas fa-info-circle"></i>
                {{__('messages.captcha_refresh_help')}}
            </div>
        </div>
    </div>
@endif
