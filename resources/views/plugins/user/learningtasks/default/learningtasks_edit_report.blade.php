{{--
 * 課題管理レポート設定画面テンプレート。
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
    <input type="hidden" name="redirect_path" value="/plugin/learningtasks/selectReport/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <h5><span class="badge badge-secondary">使用設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">レポート提出機能</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === null)
                    <input type="radio" value="" id="use_report_null" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_report_null" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_null">課題管理設定に従う（XXXXXXXXXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === 0)
                    <input type="radio" value="0" id="use_report_0" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_report_0" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === 1)
                    <input type="radio" value="1" id="use_report_1" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_report_1" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_1">この課題独自に設定する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">課題独自設定</span></h5>
    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">レポート提出機能</label>
        <div class="col-md-9 d-md-flex">

            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report" value="1" class="custom-control-input" id="use_report" @if(old("use_report", $learningtasks_posts->use_report)) checked=checked @endif>
                <label class="custom-control-label" for="use_report">提出</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_evaluate" value="1" class="custom-control-input" id="use_report_evaluate" @if(old("use_report_evaluate", $learningtasks_posts->use_report_evaluate)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate">評価</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_comment" value="1" class="custom-control-input" id="use_report_comment" @if(old("use_report_comment", $learningtasks_posts->use_report_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_comment">教員から参考資料</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">提出</label>
        <div class="col-md-9 d-md-flex">

            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_file" value="1" class="custom-control-input" id="use_report_file" @if(old("use_report_file", $learningtasks_posts->use_report_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_comment" value="1" class="custom-control-input" id="use_report_comment" @if(old("use_report_comment", $learningtasks_posts->use_report_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_comment">本文入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_report_mail" value="1" class="custom-control-input" id="use_report_mail" @if(old("use_report_mail", $learningtasks_posts->use_report_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_mail">メール送信（教員宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">評価</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_evaluate_file" value="1" class="custom-control-input" id="use_report_evaluate_file" @if(old("use_report_evaluate_file", $learningtasks_posts->use_report_evaluate_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_evaluate_comment" value="1" class="custom-control-input" id="use_report_evaluate_comment" @if(old("use_report_evaluate_comment", $learningtasks_posts->use_report_evaluate_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_report_evaluate_mail" value="1" class="custom-control-input" id="use_report_evaluate_mail" @if(old("use_report_evaluate_mail", $learningtasks_posts->use_report_evaluate_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">教員から参考資料</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_comment_file" value="1" class="custom-control-input" id="use_report_comment_file" @if(old("use_report_comment_file", $learningtasks_posts->use_report_comment_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_comment_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_comment_comment" value="1" class="custom-control-input" id="use_report_comment_comment" @if(old("use_report_comment_comment", $learningtasks_posts->use_report_comment_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_comment_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_report_comment_mail" value="1" class="custom-control-input" id="use_report_comment_mail" @if(old("use_report_comment_mail", $learningtasks_posts->use_report_comment_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_comment_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">表示方法</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_report_status_collapse" value="1" class="custom-control-input" id="use_report_status_collapse" @if(old("use_report_status_collapse", $learningtasks_posts->use_report_status_collapse)) checked=checked @endif>
                <label class="custom-control-label" for="use_report_file">履歴を開閉する</label>
            </div>
        </div>
    </div>

{{--
    <h5><span class="badge badge-secondary">使用設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">レポート提出機能</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === null)
                    <input type="radio" value="" id="use_report_null" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_report_null" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_null">課題管理設定に従う（XXXXXXXXXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report === 0)
                    <input type="radio" value="0" id="use_report_0" name="use_report" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_report_0" name="use_report" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_0">使用しない</label>
            </div><br />
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
        <label class="col-md-3 text-md-right">教員からのコメント</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report_comment === null)
                    <input type="radio" value="" id="use_report_comment_null" name="use_report_comment" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_report_comment_null" name="use_report_comment" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_comment_null">課題管理設定に従う（XXXXXXXXXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report_comment === 0)
                    <input type="radio" value="0" id="use_report_comment_0" name="use_report_comment" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_report_comment_0" name="use_report_comment" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_comment_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report_comment === 1)
                    <input type="radio" value="1" id="use_report_comment_1" name="use_report_comment" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_report_comment_1" name="use_report_comment" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_comment_1">使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">履歴の開閉</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report_status_collapse === null)
                    <input type="radio" value="" id="use_report_status_collapse_null" name="use_report_status_collapse" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_report_status_collapse_null" name="use_report_status_collapse" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_status_collapse_null">課題管理設定に従う（XXXXXXXXXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report_status_collapse === 0)
                    <input type="radio" value="0" id="use_report_status_collapse_0" name="use_report_status_collapse" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_report_status_collapse_0" name="use_report_status_collapse" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_status_collapse_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_report_status_collapse === 1)
                    <input type="radio" value="1" id="use_report_status_collapse_1" name="use_report_status_collapse" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_report_status_collapse_1" name="use_report_status_collapse" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_report_status_collapse_1">使用する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">教員への提出通知</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">メール送信</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->report_upload_mail_use == 0)
                    <input type="radio" value="0" id="report_upload_mail_use_0" name="report_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_upload.show" checked="checked">
                @else
                    <input type="radio" value="0" id="report_upload_mail_use_0" name="report_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_upload.show">
                @endif
                <label class="custom-control-label" for="report_upload_mail_use_0">送信しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->report_upload_mail_use == 1)
                    <input type="radio" value="1" id="report_upload_mail_use_1" name="report_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_upload:not(.show)" aria-expanded="true" aria-controls="collapse_report_upload" checked="checked">
                @else
                    <input type="radio" value="1" id="report_upload_mail_use_1" name="report_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_upload:not(.show)" aria-expanded="true" aria-controls="collapse_report_upload">
                @endif
                <label class="custom-control-label" for="report_upload_mail_use_1">送信する</label>
            </div><br />
        </div>
    </div>

    <div class="collapse collapse_report_upload" id ="collapse_report_upload">
        <div class="form-group row">
            <label class="col-md-3 text-md-right">定型文</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" type="button">定型文の挿入</button>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール件名<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="report_upload_mail_subject" value="{{old('report_upload_mail_subject', $learningtasks_posts->report_upload_mail_subject)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール本文<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <textarea name="report_upload_mail_text" class="form-control" rows="4">{!!old('report_upload_mail_text', $learningtasks_posts->report_upload_mail_text)!!}</textarea>
                @if ($errors && $errors->has('report_upload_mail_text')) <div class="text-danger">{{$errors->first('report_upload_mail_text')}}</div> @endif
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">受講生への評価通知</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">メール送信</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->report_evaluate_mail_use == 0)
                    <input type="radio" value="0" id="report_evaluate_mail_use_0" name="report_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_evaluate.show" checked="checked">
                @else
                    <input type="radio" value="0" id="report_evaluate_mail_use_0" name="report_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_evaluate.show">
                @endif
                <label class="custom-control-label" for="report_evaluate_mail_use_0">送信しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->report_evaluate_mail_use == 1)
                    <input type="radio" value="1" id="report_evaluate_mail_use_1" name="report_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_evaluate:not(.show)" aria-expanded="true" aria-controls="collapse_report_evaluate" checked="checked">
                @else
                    <input type="radio" value="1" id="report_evaluate_mail_use_1" name="report_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_evaluate:not(.show)" aria-expanded="true" aria-controls="collapse_report_evaluate">
                @endif
                <label class="custom-control-label" for="report_evaluate_mail_use_1">送信する</label>
            </div><br />
        </div>
    </div>

    <div class="collapse collapse_report_evaluate" id ="collapse_report_evaluate">
        <div class="form-group row">
            <label class="col-md-3 text-md-right">定型文</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" type="button">定型文の挿入</button>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール件名<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="report_evaluate_mail_subject" value="{{old('report_evaluate_mail_subject', $learningtasks_posts->report_evaluate_mail_subject)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール本文<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <textarea name="report_evaluate_mail_text" class="form-control" rows="4">{!!old('report_evaluate_mail_text', $learningtasks_posts->report_evaluate_mail_text)!!}</textarea>
                @if ($errors && $errors->has('report_evaluate_mail_text')) <div class="text-danger">{{$errors->first('report_evaluate_mail_text')}}</div> @endif
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">受講生へのコメント通知</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">メール送信</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->report_comment_mail_use == 0)
                    <input type="radio" value="0" id="report_comment_mail_use_0" name="report_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_comment.show" checked="checked">
                @else
                    <input type="radio" value="0" id="report_comment_mail_use_0" name="report_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_comment.show">
                @endif
                <label class="custom-control-label" for="report_comment_mail_use_0">送信しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->report_comment_mail_use == 1)
                    <input type="radio" value="1" id="report_comment_mail_use_1" name="report_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_comment:not(.show)" aria-expanded="true" aria-controls="collapse_report_comment" checked="checked">
                @else
                    <input type="radio" value="1" id="report_comment_mail_use_1" name="report_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_report_comment:not(.show)" aria-expanded="true" aria-controls="collapse_report_comment">
                @endif
                <label class="custom-control-label" for="report_comment_mail_use_1">送信する</label>
            </div><br />
        </div>
    </div>

    <div class="collapse collapse_report_comment" id ="collapse_report_comment">
        <div class="form-group row">
            <label class="col-md-3 text-md-right">定型文</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" type="button">定型文の挿入</button>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール件名<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="report_comment_mail_subject" value="{{old('report_comment_mail_subject', $learningtasks_posts->report_comment_mail_subject)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール本文<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <textarea name="report_comment_mail_text" class="form-control" rows="4">{!!old('report_comment_mail_text', $learningtasks_posts->report_comment_mail_text)!!}</textarea>
                @if ($errors && $errors->has('report_comment_mail_text')) <div class="text-danger">{{$errors->first('report_comment_mail_text')}}</div> @endif
            </div>
        </div>
    </div>
--}}

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
