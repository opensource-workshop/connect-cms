{{--
 * フレーム表示設定編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリスト・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.linklists.linklists_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

@if (empty($linklist->id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => $frame->plugin_name_full]) }}
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i> フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/linklists/saveView/{{$page->id}}/{{$frame_id}}/{{$linklist->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/linklists/editView/{{$page->id}}/{{$frame_id}}/{{$linklist->bucket_id}}#frame-{{$frame_id}}">

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示形式</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="type" class="form-control">
                    @foreach (LinklistType::getMembers() as $enum_value => $enum_label)
                        <option value="{{$enum_value}}" @if(old('type', $linklist_frame->type) == $enum_value) selected="selected" @endif>{{$enum_label}}</option>
                    @endforeach
                </select>
                @include('plugins.common.errors_inline', ['name' => 'type'])
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示件数</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="view_count" value="{{old('view_count', $linklist_frame->view_count)}}" class="form-control">
                @include('plugins.common.errors_inline', ['name' => 'view_count'])
                <small class="text-muted">※ 未設定時は10件</small>
            </div>
        </div>
        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    変更確定
                </span>
            </button>
        </div>
    </form>
@endif
@endsection
