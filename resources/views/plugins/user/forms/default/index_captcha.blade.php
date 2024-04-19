{{--
 * 画像認証画面テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@include('plugins.common.errors_form_line')

@if ($form->can_view_inputs_moderator)
    @can('role_article')
        <div class="row">
            <p class="text-right col">
                {{-- 集計結果ボタン --}}
                <a href="{{url('/')}}/plugin/forms/aggregate/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame->id}}" class="btn btn-success"><i class="fas fa-list"></i> 集計結果</a>
            </p>
        </div>
    @endcan
@endif

<form action="{{url('/')}}/redirect/plugin/forms/publicCaptcha/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/forms/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

    <div class="sr-only">{{$form->forms_name}}</div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">画像認証</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="mb-1">
                {!! captcha_img('flat') !!}
            </div>
            <input type="text" name="captcha" value="{{old('captcha')}}" class="form-control @if ($errors->has('captcha')) border-danger @endif">
            @include('plugins.common.errors_inline', ['name' => 'captcha'])
            <small class="text-muted">
                画像の文字を入力してください。次の画面でフォーム内容を入力することができます。
            </small>
        </div>
    </div>

    {{-- ボタンエリア --}}
    <div class="text-center">
        <button class="btn btn-primary">{{__('messages.next')}} <i class="fas fa-chevron-right"></i></button>
    </div>
</form>
@endsection
