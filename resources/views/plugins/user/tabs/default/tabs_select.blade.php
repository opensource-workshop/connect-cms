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

    @if ($frames && count($frames) > 0)
    {{-- 同ページに参照できる別フレームがあれば設定画面を表示 --}}

    <table class="mt-3">
        <tr>
            <th class="pr-3">初期選択</th>
            <th>対象フレーム</th>
        </tr>
        @foreach($frames as $frame_record)
        <tr>
            <td class="text-center">
                <div class="custom-control custom-radio">
                    {{-- 初期選択 --}}
                    <input
                        type="radio" value="{{$frame_record->id}}" id="default_frame_id{{$frame_record->id}}"
                        name="default_frame_id" class="custom-control-input"
                        @if (
                            // タブ情報があれば、該当行をチェック
                            (isset($tabs) && $tabs->default_frame_id == $frame_record->id) ||
                            // タブ情報がない（プラグイン設置後の初期表示）場合、最初の行をチェック
                            empty($tabs) && $loop->first
                        ) checked @endif
                    >
                    <label class="custom-control-label" for="default_frame_id{{$frame_record->id}}"></label>
                </div>
            </td>
            <td>
                <div class="custom-control custom-checkbox">
                    {{-- 対象フレーム --}}
                    <input
                        type="checkbox" class="custom-control-input"
                        id="frame_select{{$frame_record->id}}" name="frame_select[]" value="{{$frame_record->id}}"
                        @if ($tabs && $tabs->onFrame($frame_record->id)) checked @endif
                    >
                    <label class="custom-control-label" for="frame_select{{$frame_record->id}}" id="label_frame_select{{$frame_record->id}}">
                        {{$frame_record->frame_title}}({{$frame_record->plugin_name}})
                    </label>
                </div>
            </td>
        </tr>
        @endforeach
    </table>
    @else
        {{-- 同ページに参照できる別フレームがなければワーニングメッセージ表示 --}}
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            参照できるコンテンツがありません。同ページに他のプラグインでコンテンツを設置してください。
        </div>
    @endif

    <div class="form-group text-center mt-3">
        <div class="row">
            <div class="col-12">
                {{-- キャンセルボタン --}}
                <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
                {{-- 同ページに参照できる別フレームがあれば更新ボタンを表示 --}}
                @if ($frames && count($frames) > 0)
                    {{-- 更新ボタン --}}
                    <button type="submit" class="btn btn-primary form-horizontal">
                        <i class="fas fa-check"></i> 更新
                    </button>
                @endif
            </div>
        </div>
    </div>
</form>
@endsection
