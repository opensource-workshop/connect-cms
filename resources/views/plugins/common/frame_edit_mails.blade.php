{{--
 * メール送信設定画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.' . $frame->plugin_name . '.' . $frame->plugin_name . '_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<script type="text/javascript">
    /**
     * 全ユーザに通知
     */
    function checkbox_notice_everyone() {
        // 前方一致
        let groups_checkbox_objs = $('input[id^="notice_groups_"]');

        let all_checkbox_obj = document.getElementById('notice_everyone_id');

        for (let i = 0; i < groups_checkbox_objs.length; i++) {
            if (all_checkbox_obj.checked == true) {
                groups_checkbox_objs[i].disabled = true;
            } else {
                groups_checkbox_objs[i].disabled = false;
            }
        }
    }
</script>

@include('plugins.common.errors_form_line')

<div class="alert alert-info mt-2"><i class="fas fa-exclamation-circle"></i> メールの送信方法や送信内容を設定します。</div>

<form action="{{url('/')}}/redirect/plugin/{{$frame->plugin_name}}/saveBucketsMails/{{$page->id}}/{{$frame_id}}/{{$bucket->id}}#frame-{{$frame_id}}" method="POST" class="">
    {{csrf_field()}}

    {{-- 送信方法 --}}
    {{-- delete: 送信方法は見なくなったため、コメントアウト
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}} pt-0">送信方法</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="0" id="timing_0" name="timing" class="custom-control-input" @if(old('timing', $bucket_mail->timing) == 0) checked="checked" @endif>
                    <label class="custom-control-label" for="timing_0">即時送信</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="1" id="timing_1" name="timing" class="custom-control-input" @if(old('timing', $bucket_mail->timing) == 1) checked="checked" @endif disabled>
                    <label class="custom-control-label" for="timing_1">スケジュール送信</label>
                </div>
                <div>
                    <small class="text-muted">
                        ※ スケジュール送信は、cron 設定が必要です。（スケジュール送信は準備中です。）
                    </small>
                </div>
            </div>
        </div>
    </div>
    --}}
    <input type="hidden" value="0" name="timing">

    {{-- 投稿通知 --}}
    @if (isset($use_bucket_mail_methods) && in_array('notice', $use_bucket_mail_methods))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}} pt-0">投稿通知</label>
            <div class="{{$frame->getSettingInputClass(false)}}">
                <div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="notice_on">
                        <input type="checkbox" value="1" id="notice_on" name="notice_on" class="custom-control-input" data-toggle="collapse" data-target="#collapse_notice" aria-expanded="false" aria-controls="collapse_notice" @if(old('notice_on', $bucket_mail->notice_on) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="notice_on" id="label_notice_on">投稿通知を送る。</label>
                    </div>
                </div>
                <div class="collapse" id="collapse_notice">
                    <span class="badge badge-secondary mt-3 mb-1">タイミング</span><br />
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="notice_create">
                        <input type="checkbox" value="1" id="notice_create" name="notice_create" class="custom-control-input" @if(old('notice_create', $bucket_mail->notice_create) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="notice_create">登録</label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="notice_update">
                        <input type="checkbox" value="1" id="notice_update" name="notice_update" class="custom-control-input" @if(old('notice_update', $bucket_mail->notice_update) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="notice_update">変更</label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="notice_delete">
                        <input type="checkbox" value="1" id="notice_delete" name="notice_delete" class="custom-control-input" @if(old('notice_delete', $bucket_mail->notice_delete) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="notice_delete">削除</label>
                    </div><br />

                    <span class="badge badge-secondary mt-3 mb-1">送信先メールアドレス</span>
                    <div class="pl-0">
                        <input type="text" name="notice_addresses" value="{{old('notice_addresses', $bucket_mail->notice_addresses)}}" class="form-control @if ($errors && $errors->has('notice_addresses')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'notice_addresses'])
                        <small class="text-muted">
                            ※ 複数のメールアドレスを指定する場合は、カンマで区切ります。
                        </small>
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">送信先グループ</span><br />
                    <div class="custom-control custom-checkbox @if ($errors && $errors->has('notice_everyone')) border border-danger @endif">
                        <input type="hidden" value="" name="notice_everyone">
                        <input name="notice_everyone" value="1" type="checkbox" class="custom-control-input" id="notice_everyone_id" @if(old('notice_everyone', $bucket_mail->notice_everyone)) checked="checked" @endif onclick="checkbox_notice_everyone();">
                        <label class="custom-control-label" for="notice_everyone_id">全ユーザに通知</label>
                    </div>
                    <div class="form-control cc_scroll_checkboxes @if ($errors && $errors->has('notice_groups')) border-danger @endif">
                        @foreach ($groups as $group)
                            <div class="custom-control custom-checkbox">
                                {{-- チェック外した場合にも値を飛ばす対応 --}}
                                <input type="hidden" value="" name="notice_groups[{{$group->id}}]">
                                <input name="notice_groups[{{$group->id}}]" value="{{$group->id}}" type="checkbox" class="custom-control-input" id="notice_groups_{{$group->id}}" @if(old('notice_groups.'.$group->id, in_array($group->id, $bucket_mail->notice_groups_array))) checked="checked" @endif>
                                <label class="custom-control-label" for="notice_groups_{{$group->id}}">{{$group->name}}</label>
                            </div>
                        @endforeach
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'notice_groups'])
                    <small class="text-muted">
                        ※ 設定したグループの参加者全員に通知を送ります。<br />
                        ※ 「送信先メールアドレス」「送信先グループ」両方設定した場合、両方に通知を送ります。<br />
                    </small>

                    <span class="badge badge-secondary mt-3 mb-1">投稿通知の件名</span>
                    <div class="pl-0">
                        <input type="text" name="notice_subject" value="{{old('notice_subject', $bucket_mail->notice_subject)}}" class="form-control @if ($errors && $errors->has('notice_subject')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'notice_subject'])
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">投稿通知の本文</span>
                    <div class="form-group mb-0">
                        <textarea name="notice_body" class="form-control" rows=5>{!!old('notice_body', $bucket_mail->notice_body)!!}</textarea>
                    </div>

                    @includeFirst(["plugins.user.{$frame->plugin_name}.description_frame_mails", 'plugins.common.description_frame_mails'])
                </div>
            </div>
        </div>
    @endif

    {{-- 関連記事通知 --}}
    @if (isset($use_bucket_mail_methods) && in_array('relate', $use_bucket_mail_methods))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}} pt-0">関連記事通知</label>
            <div class="{{$frame->getSettingInputClass(false)}}">
                <div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="relate_on">
                        <input type="checkbox" value="1" id="relate_on" name="relate_on" class="custom-control-input" data-toggle="collapse" data-target="#collapse_relate" aria-expanded="false" aria-controls="collapse_relate" @if(old('relate_on', $bucket_mail->relate_on) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="relate_on" id="label_relate_on">関連記事の投稿通知を送る。</label>
                    </div>
                </div>
                <div class="collapse" id="collapse_relate">
                    <span class="badge badge-secondary mt-3 mb-1">関連記事通知の件名</span>
                    <div class="pl-0">
                        <input type="text" name="relate_subject" value="{{old('relate_subject', $bucket_mail->relate_subject)}}" class="form-control @if ($errors && $errors->has('relate_subject')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'relate_subject'])
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">関連記事通知の本文</span>
                    <div class="form-group mb-0">
                        <textarea name="relate_body" class="form-control" rows=5>{!!old('relate_body', $bucket_mail->relate_body)!!}</textarea>
                    </div>

                    @includeFirst(["plugins.user.{$frame->plugin_name}.description_frame_mails", 'plugins.common.description_frame_mails'])
                </div>
            </div>
        </div>
    @endif

    {{-- 承認通知 --}}
    @if (isset($use_bucket_mail_methods) && in_array('approval', $use_bucket_mail_methods))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}} pt-0">承認通知</label>
            <div class="{{$frame->getSettingInputClass(false)}}">
                <div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="approval_on">
                        <input type="checkbox" value="1" id="approval_on" name="approval_on" class="custom-control-input" data-toggle="collapse" data-target="#collapse_approval" aria-expanded="false" aria-controls="collapse_approval" @if(old('approval_on', $bucket_mail->approval_on) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="approval_on" id="label_approval_on">承認通知を送る。</label>
                    </div>
                </div>
                <div class="collapse" id="collapse_approval">
                    <span class="badge badge-secondary mt-3 mb-1">送信先メールアドレス</span>
                    <div class="pl-0">
                        <input type="text" name="approval_addresses" value="{{old('approval_addresses', $bucket_mail->approval_addresses)}}" class="form-control @if ($errors && $errors->has('approval_addresses')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'approval_addresses'])
                        <small class="text-muted">
                            ※ 複数のメールアドレスを指定する場合は、カンマで区切ります。
                        </small>
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">送信先グループ</span>
                    <div class="form-control cc_scroll_checkboxes @if ($errors && $errors->has('approval_groups')) border-danger @endif">
                        @foreach ($groups as $group)
                            <div class="custom-control custom-checkbox">
                                {{-- チェック外した場合にも値を飛ばす対応 --}}
                                <input type="hidden" value="" name="approval_groups[{{$group->id}}]">
                                <input name="approval_groups[{{$group->id}}]" value="{{$group->id}}" type="checkbox" class="custom-control-input" id="approval_groups_{{$group->id}}" @if(old('approval_groups.'.$group->id, in_array($group->id, $bucket_mail->approval_groups_array))) checked="checked" @endif>
                                <label class="custom-control-label" for="approval_groups_{{$group->id}}">{{$group->name}}</label>
                            </div>
                        @endforeach
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'approval_groups'])
                    <small class="text-muted">
                        ※ 設定したグループの参加者全員に通知を送ります。<br />
                        ※ 「送信先メールアドレス」「送信先グループ」両方設定した場合、両方に通知を送ります。<br />
                    </small>

                    <span class="badge badge-secondary mt-3 mb-1">承認通知の件名</span>
                    <div class="pl-0">
                        <input type="text" name="approval_subject" value="{{old('approval_subject', $bucket_mail->approval_subject)}}" class="form-control @if ($errors && $errors->has('approval_subject')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'approval_subject'])
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">承認通知の本文</span>
                    <div class="form-group mb-0">
                        <textarea name="approval_body" class="form-control" rows=5>{!!old('approval_body', $bucket_mail->approval_body)!!}</textarea>
                    </div>

                    @includeFirst(["plugins.user.{$frame->plugin_name}.description_frame_mails", 'plugins.common.description_frame_mails'])
                </div>
            </div>
        </div>
    @endif

    {{-- 承認済み通知 --}}
    @if (isset($use_bucket_mail_methods) && in_array('approved', $use_bucket_mail_methods))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}} pt-0">承認済み通知</label>
            <div class="{{$frame->getSettingInputClass(false)}}">
                <div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="hidden" value="" name="approved_on">
                        <input type="checkbox" value="1" id="approved_on" name="approved_on" class="custom-control-input" data-toggle="collapse" data-target="#collapse_approved" aria-expanded="false" aria-controls="collapse_approved" @if(old('approved_on', $bucket_mail->approved_on) == 1) checked="checked" @endif>
                        <label class="custom-control-label" for="approved_on" id="label_approved_on">承認済み通知を送る。</label>
                    </div>
                </div>
                <div class="collapse" id="collapse_approved">
                    <span class="badge badge-secondary mt-3">投稿者への通知</span>
                    <div class="pl-0 mb-3">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            {{-- チェック外した場合にも値を飛ばす対応 --}}
                            <input type="hidden" value="" name="approved_author">

                            <input type="checkbox" value="1" id="approved_author" name="approved_author" class="custom-control-input @if ($errors && $errors->has('approved_author')) is-invalid @endif" @if(old('approved_author', $bucket_mail->approved_author) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="approved_author">投稿者へ通知する</label>
                        </div>
                        @include('plugins.common.errors_inline', ['name' => 'approved_author'])
                    </div>

                    <span class="badge badge-secondary mb-1">送信先メールアドレス</span>
                    <div class="pl-0">
                        <input type="text" name="approved_addresses" value="{{old('approved_addresses', $bucket_mail->approved_addresses)}}" class="form-control @if ($errors && $errors->has('approved_addresses')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'approved_addresses'])
                        <small class="text-muted">
                            ※ 投稿者以外に送る場合。複数のメールアドレスを指定する場合は、カンマで区切ります。
                        </small>
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">送信先グループ</span>
                    <div class="form-control cc_scroll_checkboxes @if ($errors && $errors->has('approved_groups')) border-danger @endif">
                        @foreach ($groups as $group)
                            <div class="custom-control custom-checkbox">
                                {{-- チェック外した場合にも値を飛ばす対応 --}}
                                <input type="hidden" value="0" name="approved_groups[{{$group->id}}]">
                                <input name="approved_groups[{{$group->id}}]" value="{{$group->id}}" type="checkbox" class="custom-control-input" id="approved_groups_{{$group->id}}" @if(old('approved_groups.'.$group->id, in_array($group->id, $bucket_mail->approved_groups_array))) checked="checked" @endif>
                                <label class="custom-control-label" for="approved_groups_{{$group->id}}">{{$group->name}}</label>
                            </div>
                        @endforeach
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'approved_groups'])
                    <small class="text-muted">
                        ※ 設定したグループの参加者全員に通知を送ります。<br />
                        ※ 「投稿者への通知」「送信先メールアドレス」「送信先グループ」を全て設定した場合、全て通知を送ります。<br />
                    </small>

                    <span class="badge badge-secondary mt-3 mb-1">承認済み通知の件名</span>
                    <div class="pl-0">
                        <input type="text" name="approved_subject" value="{{old('approved_subject', $bucket_mail->approved_subject)}}" class="form-control @if ($errors && $errors->has('approved_subject')) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'approved_subject'])
                    </div>

                    <span class="badge badge-secondary mt-3 mb-1">承認済み通知の本文</span>
                    <div class="form-group">
                        <textarea name="approved_body" class="form-control" rows=5>{!!old('approved_body', $bucket_mail->approved_body)!!}</textarea>
                    </div>

                    @includeFirst(["plugins.user.{$frame->plugin_name}.description_frame_mails", 'plugins.common.description_frame_mails'])
                </div>
            </div>
        </div>
    @endif

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i> キャンセル</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 更新</button>
    </div>
</form>

{{-- 初期状態で開くもの --}}
@if (isset($use_bucket_mail_methods) && in_array('notice', $use_bucket_mail_methods))
    @if (old('notice_on', $bucket_mail->notice_on))
        <script>
        $('#collapse_notice').collapse({
            toggle: true
        })
        </script>
    @endif

    <script>
        checkbox_notice_everyone();
    </script>
@endif

@if (isset($use_bucket_mail_methods) && in_array('relate', $use_bucket_mail_methods))
    @if (old('relate_on', $bucket_mail->relate_on))
        <script>
        $('#collapse_relate').collapse({
            toggle: true
        })
        </script>
    @endif
@endif

@if (isset($use_bucket_mail_methods) && in_array('approval', $use_bucket_mail_methods))
    @if (old('approval_on', $bucket_mail->approval_on))
        <script>
        $('#collapse_approval').collapse({
            toggle: true
        })
        </script>
    @endif
@endif

@if (isset($use_bucket_mail_methods) && in_array('approved', $use_bucket_mail_methods))
    @if (old('approved_on', $bucket_mail->approved_on))
        <script>
        $('#collapse_approved').collapse({
            toggle: true
        })
        </script>
    @endif
@endif

@endsection
