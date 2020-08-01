{{--
 * 課題管理記事登録画面テンプレート。
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
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="/plugin/learningtasks/editExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <h5><span class="badge badge-secondary">使用設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験提出機能</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination === null)
                    <input type="radio" value="" id="examination_null" name="use_examination" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="examination_null" name="use_examination" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="examination_null">課題管理設定に従う（XXXXXXXXXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination === 0)
                    <input type="radio" value="0" id="use_examination_0" name="use_examination" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_examination_0" name="use_examination" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination === 1)
                    <input type="radio" value="1" id="use_examination_1" name="use_examination" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_examination_1" name="use_examination" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_1">この課題独自に設定する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">課題独自設定</span></h5>
    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">試験提出機能</label>
        <div class="col-md-9 d-md-flex">

            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination" value="1" class="custom-control-input" id="use_examination" @if(old("use_examination", $learningtasks_posts->use_examination)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination">提出</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_evaluate" value="1" class="custom-control-input" id="use_examination_evaluate" @if(old("use_examination_evaluate", $learningtasks_posts->use_examination_evaluate)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate">評価</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_comment" value="1" class="custom-control-input" id="use_examination_comment" @if(old("use_examination_comment", $learningtasks_posts->use_examination_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_comment">教員から参考資料</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">提出</label>
        <div class="col-md-9 d-md-flex">

            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_file" value="1" class="custom-control-input" id="use_examination_file" @if(old("use_examination_file", $learningtasks_posts->use_examination_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_file" value="1" class="custom-control-input" id="use_examination_file" @if(old("use_examination_file", $learningtasks_posts->use_examination_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_file">本文入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_examination_mail" value="1" class="custom-control-input" id="use_examination_mail" @if(old("use_examination_mail", $learningtasks_posts->use_examination_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_mail">メール送信（教員宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">評価</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_evaluate_file" value="1" class="custom-control-input" id="use_examination_evaluate_file" @if(old("use_examination_evaluate_file", $learningtasks_posts->use_examination_evaluate_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_evaluate_comment" value="1" class="custom-control-input" id="use_examination_evaluate_comment" @if(old("use_examination_evaluate_comment", $learningtasks_posts->use_examination_evaluate_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_examination_evaluate_mail" value="1" class="custom-control-input" id="use_examination_evaluate_mail" @if(old("use_examination_evaluate_mail", $learningtasks_posts->use_examination_evaluate_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 text-md-right">教員から参考資料</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_comment_file" value="1" class="custom-control-input" id="use_examination_comment_file" @if(old("use_examination_comment_file", $learningtasks_posts->use_examination_comment_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_comment_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_comment_comment" value="1" class="custom-control-input" id="use_examination_comment_comment" @if(old("use_examination_comment_comment", $learningtasks_posts->use_examination_comment_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_comment_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_examination_comment_mail" value="1" class="custom-control-input" id="use_examination_comment_mail" @if(old("use_examination_comment_mail", $learningtasks_posts->use_examination_comment_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_comment_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">表示方法</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_examination_status_collapse" value="1" class="custom-control-input" id="use_examination_status_collapse" @if(old("use_examination_status_collapse", $learningtasks_posts->use_examination_status_collapse)) checked=checked @endif>
                <label class="custom-control-label" for="use_examination_status_collapse">履歴を開閉する</label>
            </div>
        </div>
    </div>

{{--
    <h5><span class="badge badge-secondary">使用設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">レポート試験機能</label>
        <div class="col-md-9">
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
            </div><br />
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
        <label class="col-md-3 text-md-right">添削アップロード</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_correction === null)
                    <input type="radio" value="" id="use_examination_correction_null" name="use_examination_correction" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_examination_correction_null" name="use_examination_correction" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_correction_null">課題管理設定に従う（XXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_correction === 0)
                    <input type="radio" value="0" id="use_examination_correction_0" name="use_examination_correction" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_examination_correction_0" name="use_examination_correction" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_correction_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_correction === 1)
                    <input type="radio" value="1" id="use_examination_correction_1" name="use_examination_correction" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_examination_correction_1" name="use_examination_correction" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_correction_1">使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験コメント</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_comment === null)
                    <input type="radio" value="" id="use_examination_comment_null" name="use_examination_comment" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_examination_comment_null" name="use_examination_comment" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_comment_null">課題管理設定に従う（XXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_comment === 0)
                    <input type="radio" value="0" id="use_examination_comment_0" name="use_examination_comment" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_examination_comment_0" name="use_examination_comment" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_comment_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_comment === 1)
                    <input type="radio" value="1" id="use_examination_comment_1" name="use_examination_comment" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_examination_comment_1" name="use_examination_comment" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_comment_1">使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">履歴の開閉</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_status_collapse === null)
                    <input type="radio" value="" id="use_examination_status_collapse_null" name="use_examination_status_collapse" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_examination_status_collapse_null" name="use_examination_status_collapse" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_status_collapse_null">課題管理設定に従う（XXXXXXXXXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_status_collapse === 0)
                    <input type="radio" value="0" id="use_examination_status_collapse_0" name="use_examination_status_collapse" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_examination_status_collapse_0" name="use_examination_status_collapse" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_status_collapse_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_examination_status_collapse === 1)
                    <input type="radio" value="1" id="use_examination_status_collapse_1" name="use_examination_status_collapse" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_examination_status_collapse_1" name="use_examination_status_collapse" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_examination_status_collapse_1">使用する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">教員への提出通知</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">メール送信</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_upload_mail_use == 0)
                    <input type="radio" value="0" id="examination_upload_mail_use_0" name="examination_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_upload.show" checked="checked">
                @else
                    <input type="radio" value="0" id="examination_upload_mail_use_0" name="examination_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_upload.show">
                @endif
                <label class="custom-control-label" for="examination_upload_mail_use_0">送信しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_upload_mail_use == 1)
                    <input type="radio" value="1" id="examination_upload_mail_use_1" name="examination_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_upload:not(.show)" aria-expanded="true" aria-controls="collapse_examination_upload" checked="checked">
                @else
                    <input type="radio" value="1" id="examination_upload_mail_use_1" name="examination_upload_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_upload:not(.show)" aria-expanded="true" aria-controls="collapse_examination_upload">
                @endif
                <label class="custom-control-label" for="examination_upload_mail_use_1">送信する</label>
            </div><br />
        </div>
    </div>

    <div class="collapse collapse_examination_upload" id ="collapse_examination_upload">
        <div class="form-group row">
            <label class="col-md-3 text-md-right">定型文</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" type="button">定型文の挿入</button>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール件名<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="examination_upload_mail_subject" value="{{old('examination_upload_mail_subject', $learningtasks_posts->examination_upload_mail_subject)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール本文<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <textarea name="examination_upload_mail_text" class="form-control" rows="4">{!!old('examination_upload_mail_text', $learningtasks_posts->examination_upload_mail_text)!!}</textarea>
                @if ($errors && $errors->has('examination_upload_mail_text')) <div class="text-danger">{{$errors->first('examination_upload_mail_text')}}</div> @endif
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">受講生への評価通知</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">メール送信</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_evaluate_mail_use == 0)
                    <input type="radio" value="0" id="examination_evaluate_mail_use_0" name="examination_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_evaluate.show" checked="checked">
                @else
                    <input type="radio" value="0" id="examination_evaluate_mail_use_0" name="examination_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_evaluate.show">
                @endif
                <label class="custom-control-label" for="examination_evaluate_mail_use_0">送信しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_evaluate_mail_use == 1)
                    <input type="radio" value="1" id="examination_evaluate_mail_use_1" name="examination_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_evaluate:not(.show)" aria-expanded="true" aria-controls="collapse_examination_evaluate" checked="checked">
                @else
                    <input type="radio" value="1" id="examination_evaluate_mail_use_1" name="examination_evaluate_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_evaluate:not(.show)" aria-expanded="true" aria-controls="collapse_examination_evaluate">
                @endif
                <label class="custom-control-label" for="examination_evaluate_mail_use_1">送信する</label>
            </div><br />
        </div>
    </div>

    <div class="collapse collapse_examination_evaluate" id ="collapse_examination_evaluate">
        <div class="form-group row">
            <label class="col-md-3 text-md-right">定型文</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" type="button">定型文の挿入</button>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール件名<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="examination_evaluate_mail_subject" value="{{old('examination_evaluate_mail_subject', $learningtasks_posts->examination_evaluate_mail_subject)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール本文<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <textarea name="examination_evaluate_mail_text" class="form-control" rows="4">{!!old('examination_evaluate_mail_text', $learningtasks_posts->examination_evaluate_mail_text)!!}</textarea>
                @if ($errors && $errors->has('examination_evaluate_mail_text')) <div class="text-danger">{{$errors->first('examination_evaluate_mail_text')}}</div> @endif
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">受講生へのコメント通知</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">メール送信</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_comment_mail_use == 0)
                    <input type="radio" value="0" id="examination_comment_mail_use_0" name="examination_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_comment.show" checked="checked">
                @else
                    <input type="radio" value="0" id="examination_comment_mail_use_0" name="examination_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_comment.show">
                @endif
                <label class="custom-control-label" for="examination_comment_mail_use_0">送信しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_comment_mail_use == 1)
                    <input type="radio" value="1" id="examination_comment_mail_use_1" name="examination_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_comment:not(.show)" aria-expanded="true" aria-controls="collapse_examination_comment" checked="checked">
                @else
                    <input type="radio" value="1" id="examination_comment_mail_use_1" name="examination_comment_mail_use" class="custom-control-input" data-toggle="collapse" data-target="#collapse_examination_comment:not(.show)" aria-expanded="true" aria-controls="collapse_examination_comment">
                @endif
                <label class="custom-control-label" for="examination_comment_mail_use_1">送信する</label>
            </div><br />
        </div>
    </div>

    <div class="collapse collapse_examination_comment" id ="collapse_examination_comment">
        <div class="form-group row">
            <label class="col-md-3 text-md-right">定型文</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" type="button">定型文の挿入</button>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール件名<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="examination_comment_mail_subject" value="{{old('examination_comment_mail_subject', $learningtasks_posts->examination_comment_mail_subject)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">メール本文<label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <textarea name="examination_comment_mail_text" class="form-control" rows="4">{!!old('examination_comment_mail_text', $learningtasks_posts->examination_comment_mail_text)!!}</textarea>
                @if ($errors && $errors->has('examination_comment_mail_text')) <div class="text-danger">{{$errors->first('examination_comment_mail_text')}}</div> @endif
            </div>
        </div>
    </div>
--}}

    <h5><span class="badge badge-secondary">申し込み設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">申し込み可能判定</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_timing == 0)
                    <input type="radio" value="0" id="examination_timing_0" name="examination_timing" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="examination_timing_0" name="examination_timing" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="examination_timing_0">レポートが合格してから</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_timing == 1)
                    <input type="radio" value="1" id="examination_timing_1" name="examination_timing" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="examination_timing_1" name="examination_timing" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="examination_timing_1">レポートが1回でも提出済みなら（合否のチェックはしない）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->examination_timing == 2)
                    <input type="radio" value="2" id="examination_timing_2" name="examination_timing" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="2" id="examination_timing_2" name="examination_timing" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="examination_timing_2">レポートが提出済み＆最新が不合格ではない（合格のチェックはしない）</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">日時設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験日時一覧</label>
        <div class="col-md-9">
            <div class="card p-2">
            @if ($examinations->count() == 0)
                ※ 設定されている試験がありません。
            @else
            @foreach ($examinations as $examination)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="del_examinations[{{$examination->id}}]" value="1" class="custom-control-input" id="del_examinations[{{$examination->id}}]" @if(old("del_examination.$examination->id")) checked=checked @endif>
                    <label class="custom-control-label" for="del_examinations[{{$examination->id}}]">
                        {{$examination->start_at->format('Y-m-d H:i')}} ～ {{$examination->end_at->format('Y-m-d H:i')}}
                    </label>
                </div>
            @endforeach
            @endif
            </div>
            <small class="text-muted">削除する場合はチェックします。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験日時追加</label>
        <div class="col-md-4">
            <div class="input-group date" id="start_at" data-target-input="nearest">
                <input type="text" name="start_at" value="{{old('start_at')}}" class="form-control datetimepicker-input" data-target="#start_at" placeholder="開始日時">
                <div class="input-group-append" data-target="#start_at" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('start_at')) <div class="text-danger">{{$errors->first('start_at')}}</div> @endif
        </div>
        <div class="col-md-4">
            <div class="input-group date" id="end_at" data-target-input="nearest">
                <input type="text" name="end_at" value="{{old('end_at')}}" class="form-control datetimepicker-input" data-target="#end_at" placeholder="終了日時">
                <div class="input-group-append" data-target="#end_at" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('end_at')) <div class="text-danger">{{$errors->first('end_at')}}</div> @endif
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $('#start_at').datetimepicker({
                locale: 'ja',
                sideBySide: true,
                dayViewHeaderFormat: 'YYYY年 M月',
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#end_at').datetimepicker({
                locale: 'ja',
                sideBySide: true,
                dayViewHeaderFormat: 'YYYY年 M月',
                format: 'YYYY-MM-DD HH:mm'
            });
        });
    </script>

    <h5><span class="badge badge-secondary">問題設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">ファイル一覧</label>
        <div class="col-md-9">
            <div class="card p-2">
            @isset($post_files)
            @foreach($post_files as $examination_file)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="del_task_file[{{$examination_file->id}}]" value="1" class="custom-control-input" id="del_task_file[{{$examination_file->id}}]" @if(old("del_task_file.$examination_file->id")) checked=checked @endif>
                    <label class="custom-control-label" for="del_task_file[{{$examination_file->id}}]"><a href="{{url('/')}}/file/{{$examination_file->upload_id}}" target="_blank" rel="noopener">{{$examination_file->client_original_name}}</a></label>
                </div>
            @endforeach
            @else
                <div class="card-body p-0">
                    試験関係のファイルは添付されていません。
                </div>
            @endisset
            </div>
            <small class="text-muted">削除する場合はチェックします。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right" for="add_task_file">試験問題など</label>
        <div class="col-md-9">
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="add_task_file" name="add_task_file">
                <label class="custom-file-label" for="add_task_file" data-browse="参照">試験問題など</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($learningtasks_posts->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span> キャンセル</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($learningtasks_posts->id))
                        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endif
<script>
$('.custom-file-input').on('change',function(){
    $(this).next('.custom-file-label').html($(this)[0].files[0].name);
})
</script>
@endsection
