{{--
 * 課題管理・レポート設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

{{-- 試験設定フォーム --}}
@if (empty($learningtasks_posts->id))
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">課題データを作成してから、試験の設定をしてください。</p>
        </div>
    </div>
@else
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveReport/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_users_post">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="/plugin/learningtasks/editReport/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <h5><span class="badge badge-secondary">使用項目の設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">レポート提出機能</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if(empty($tool->getFunction('post_report_setting', true)))
                    <input type="radio" value="" id="post_report_setting_null" name="post_report_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_report.show">
                @else
                    <input type="radio" value="" id="post_report_setting_null" name="post_report_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_report.show">
                @endif
                <label class="custom-control-label" for="post_report_setting_null">課題管理設定に従う</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($tool->getFunction('post_report_setting', true) == 'off')
                    <input type="radio" value="off" id="post_report_setting_off" name="post_report_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_report.show">
                @else
                    <input type="radio" value="off" id="post_report_setting_off" name="post_report_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_report.show">
                @endif
                <label class="custom-control-label" for="post_report_setting_off">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($tool->getFunction('post_report_setting', true) == 'on')
                    <input type="radio" value="on" id="post_report_setting_on" name="post_report_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_report:not(.show)" aria-expanded="true" aria-controls="collapse_post_report">
                @else
                    <input type="radio" value="on" id="post_report_setting_on" name="post_report_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_report:not(.show)" aria-expanded="true" aria-controls="collapse_post_report">
                @endif
                <label class="custom-control-label" for="post_report_setting_on">この課題独自に設定する</label>
            </div>
        </div>
    </div>

    {{-- 独自設定の場合のみ表示、その他は隠す --}}
    <div class="collapse {{$tool->getSettingShowstr("post_report_setting")}} collapse_post_report" id="collapse_post_report">
        <h5><span class="badge badge-secondary">課題独自の項目設定</span></h5>
        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">レポート提出機能</label>
            <div class="col-md-9 d-md-flex">

                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report]" value="on" class="custom-control-input" id="use_report" @if(old("post_settings.use_report", $tool->getFunction('use_report', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report">提出</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_evaluate]" value="on" class="custom-control-input" id="use_report_evaluate" @if(old("post_settings.use_report_evaluate", $tool->getFunction('use_report_evaluate', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_evaluate">評価</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_reference]" value="on" class="custom-control-input" id="use_report_reference" @if(old("post_settings.use_report_reference", $tool->getFunction('use_report_reference', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_reference">教員から参考資料</label>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">提出</label>
            <div class="col-md-9 d-md-flex">

                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_file]" value="on" class="custom-control-input" id="use_report_file" @if(old("post_settings.use_report_file", $tool->getFunction('use_report_file', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_file">アップロード</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_comment]" value="on" class="custom-control-input" id="use_report_comment" @if(old("post_settings.use_report_comment", $tool->getFunction('use_report_comment', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_comment">本文入力</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="post_settings[use_report_mail]" value="on" class="custom-control-input" id="use_report_mail" @if(old("post_settings.use_report_mail", $tool->getFunction('use_report_mail', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_mail">メール送信（教員宛）</label>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">評価</label>
            <div class="col-md-9 d-md-flex">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_evaluate_file]" value="on" class="custom-control-input" id="use_report_evaluate_file" @if(old("post_settings.use_report_evaluate_file", $tool->getFunction('use_report_evaluate_file', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_evaluate_file">アップロード</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_evaluate_comment]" value="on" class="custom-control-input" id="use_report_evaluate_comment" @if(old("post_settings.use_report_evaluate_comment", $tool->getFunction('use_report_evaluate_comment', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_evaluate_comment">コメント入力</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="post_settings[use_report_evaluate_mail]" value="on" class="custom-control-input" id="use_report_evaluate_mail" @if(old("post_settings.use_report_evaluate_mail", $tool->getFunction('use_report_evaluate_mail', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_evaluate_mail">メール送信（受講者宛）</label>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">教員から参考資料</label>
            <div class="col-md-9 d-md-flex">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_reference_file]" value="on" class="custom-control-input" id="use_report_reference_file" @if(old("post_settings.use_report_reference_file", $tool->getFunction('use_report_reference_file', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_reference_file">アップロード</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_reference_comment]" value="on" class="custom-control-input" id="use_report_reference_comment" @if(old("post_settings.use_report_reference_comment", $tool->getFunction('use_report_reference_comment', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_reference_comment">コメント入力</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="post_settings[use_report_reference_mail]" value="on" class="custom-control-input" id="use_report_reference_mail" @if(old("post_settings.use_report_reference_mail", $tool->getFunction('use_report_reference_mail', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_reference_mail">メール送信（受講者宛）</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">表示方法</label>
            <div class="col-md-9 d-md-flex">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_report_status_collapse]" value="on" class="custom-control-input" id="use_report_status_collapse" @if(old("post_settings.use_report_status_collapse", $tool->getFunction('use_report_status_collapse', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_report_status_collapse">履歴を開閉する</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span> キャンセル</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endif
@endsection
