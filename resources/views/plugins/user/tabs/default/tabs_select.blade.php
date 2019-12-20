{{--
 * フレーム選択画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category タブ・プラグイン
 --}}

{{-- 機能選択タブ --}}
<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.tabs.tabs_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

<form action="/plugin/tabs/saveSelect/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame->id}}" name="tabs_form" method="POST">
    {{ csrf_field() }}

    @if ($frames)
    <table class="mt-3">
        <tr>
            <th class="pr-3">初期選択</th>
            <th>対象フレーム</th>
        </tr>
        @foreach($frames as $frame)
        <tr>
            <td class="text-center">
                <div class="custom-control custom-radio">
                    @if(isset($tabs) && $tabs->default_frame_id == $frame->id)
                    <input type="radio" value="{{$frame->id}}" id="default_frame_id{{$frame->id}}" name="default_frame_id" class="custom-control-input" checked="checked">
                    @else
                    <input type="radio" value="{{$frame->id}}" id="default_frame_id{{$frame->id}}" name="default_frame_id" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="default_frame_id{{$frame->id}}"></label>
                </div>
            </td>
            <td>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="frame_select{{$frame->id}}" name="frame_select[]" value="{{$frame->id}}" @if ($tabs && $tabs->onFrame($frame->id)) checked @endif />
                    <label class="custom-control-label" for="frame_select{{$frame->id}}">
                        {{$frame->frame_title}}({{$frame->plugin_name}})
                    </label>
                </div>
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="form-group row mx-0">
        <div class="offset-md-3">
            <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </div>
</form>

