{{--
 * 設定画面のワーニングメッセージのテンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<div class="alert alert-warning mt-2">
    @foreach ($warning_messages as $warning_message)
        <i class="fas fa-exclamation-circle"></i> {!! nl2br(e($warning_message)) !!}<br>
    @endforeach
</div>
@endsection
