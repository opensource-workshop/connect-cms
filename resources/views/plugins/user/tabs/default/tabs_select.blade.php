{{--
 * フレーム選択画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category タブ・プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.tabs.tabs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/tabs/saveSelect/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame->id}}" name="tabs_form" method="POST">
    {{ csrf_field() }}

    @if ($frames)
    <table class="mt-3">
        <tr>
            <th class="pr-3">初期選択</th>
            <th>対象フレーム</th>
        </tr>
        @foreach($frames as $frame_record)
        <tr>
            <td class="text-center">
                <div class="custom-control custom-radio">
                    @if(isset($tabs) && $tabs->default_frame_id == $frame_record->id)
                    <input type="radio" value="{{$frame_record->id}}" id="default_frame_id{{$frame_record->id}}" name="default_frame_id" class="custom-control-input" checked="checked">
                    @else
                    <input type="radio" value="{{$frame_record->id}}" id="default_frame_id{{$frame_record->id}}" name="default_frame_id" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="default_frame_id{{$frame_record->id}}"></label>
                </div>
            </td>
            <td>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="frame_select{{$frame_record->id}}" name="frame_select[]" value="{{$frame_record->id}}" @if ($tabs && $tabs->onFrame($frame_record->id)) checked @endif />
                    <label class="custom-control-label" for="frame_select{{$frame_record->id}}">
                        {{$frame_record->frame_title}}({{$frame_record->plugin_name}})
                    </label>
                </div>
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="form-group text-center mt-3">
        <div class="row">
            <div class="col-12">
                <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
        </div>
    </div>
</form>
@endsection
