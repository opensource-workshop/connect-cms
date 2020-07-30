{{--
 * 課題管理機能選択画面テンプレート。
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
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveFunction/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_users_post">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="/plugin/learningtasks/selectFunction/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3">レポート提出機能</label>
        <div class="col-sm-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === null)
                    <input type="radio" value="" id="use_report_null" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_report_null" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_null">課題管理設定に従う（{{$learningtask->strUseReport()}}）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === 0)
                    <input type="radio" value="0" id="use_report_0" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_report_0" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_0">使用しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === 1)
                    <input type="radio" value="1" id="use_report_1" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_report_1" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_1">使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3">レポート試験機能</label>
        <div class="col-sm-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination === null)
                    <input type="radio" value="" id="use_examination_null" name="use_examination" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_examination_null" name="use_examination" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_null">課題管理設定に従う（XXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination === 0)
                    <input type="radio" value="0" id="use_examination_0" name="use_examination" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_examination_0" name="use_examination" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_0">使用しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination === 1)
                    <input type="radio" value="1" id="use_examination_1" name="use_examination" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_examination_1" name="use_examination" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_1">使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3">総合評価機能</label>
        <div class="col-sm-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_evaluate === null)
                    <input type="radio" value="" id="use_evaluate_null" name="use_evaluate" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_evaluate_null" name="use_evaluate" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_evaluate_null">課題管理設定に従う（XXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_evaluate === 0)
                    <input type="radio" value="0" id="use_evaluate_0" name="use_evaluate" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_evaluate_0" name="use_evaluate" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_evaluate_0">使用しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_evaluate === 1)
                    <input type="radio" value="1" id="use_evaluate_1" name="use_evaluate" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_evaluate_1" name="use_evaluate" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_evaluate_1">使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3">ログインユーザのみ課題の閲覧を許可</label>
        <div class="col-sm-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->need_auth === null)
                    <input type="radio" value="" id="need_auth_null" name="need_auth" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="need_auth_null" name="need_auth" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="need_auth_null">課題管理設定に従う（XXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->need_auth === 0)
                    <input type="radio" value="0" id="need_auth_0" name="need_auth" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="need_auth_0" name="need_auth" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="need_auth_0">使用しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->need_auth === 1)
                    <input type="radio" value="1" id="need_auth_1" name="need_auth" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="need_auth_1" name="need_auth" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="need_auth_1">使用する</label>
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
