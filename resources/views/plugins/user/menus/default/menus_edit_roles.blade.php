{{--
 * メニュー権限設定画面
 *
 * @category メニュープラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    @include('plugins.user.menus.menus_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@include('plugins.common.flash_message_for_frame')

@php
    $allow_moderator_edit = (bool) FrameConfig::getConfigValue($frame_configs, MenuFrameConfig::menu_allow_moderator_edit, 0);
@endphp

<form action="{{ url('/') }}/redirect/plugin/menus/saveFrameRoles/{{ $page->id }}/{{ $frame_id }}#frame-{{ $frame->id }}" method="POST" class="mt-3">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{ url('/') }}/plugin/menus/editFrameRoles/{{ $page->id }}/{{ $frame_id }}#frame-{{ $frame->id }}">

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="{{MenuFrameConfig::menu_allow_moderator_edit}}" value="0">
            <input type="checkbox" class="custom-control-input" id="{{MenuFrameConfig::menu_allow_moderator_edit}}" name="{{MenuFrameConfig::menu_allow_moderator_edit}}" value="1" @if ($allow_moderator_edit) checked @endif>
            <label class="custom-control-label" for="{{MenuFrameConfig::menu_allow_moderator_edit}}">モデレータにメニュー項目の編集を許可する</label>
        </div>
        <small class="form-text text-muted">有効にすると、モデレータおよびプラグイン管理者にメニューの編集ボタンが表示されます。</small>
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 更新</button>
    </div>
</form>
@endsection
