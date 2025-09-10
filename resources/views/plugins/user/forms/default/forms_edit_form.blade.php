{{--
 * フォーム編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@include('plugins.common.errors_form_line')

{{-- 以下のアドレスにメール送信する=on, 登録者にメール送信する=on, 登録者に仮登録メールを送信する=on --}}
@if (old('mail_send_flag', $form->mail_send_flag) || old('user_mail_send_flag', $form->user_mail_send_flag) || old('use_temporary_regist_mail_flag', $form->use_temporary_regist_mail_flag))
    @include('plugins.common.error_system_mail_setting')
@endif

@if (!$form->id && !$create_flag)
    @include('plugins.user.forms.default.forms_warning_messages_line', ['warning_messages' => ['フォーム選択から選択するか、フォーム作成で作成してください。']])
@else

    <div class="alert alert-info mt-2"><i class="fas fa-exclamation-circle"></i>

    @if ($message)
        {!!$message!!}
    @else
        @if (empty($form) || $create_flag)
            新しいフォーム設定を登録します。
        @else
            フォーム設定を変更します。
        @endif
    @endif
    </div>
@endif

@if (!$form->id && !$create_flag)
@else

{{-- create_flag がtrue の場合、新規作成するためにforms_id をセットしない --}}
<form action="{{url('/')}}/plugin/forms/saveBuckets/{{$page->id}}/{{$frame_id}}@if(!$create_flag)/{{$form->id}}@endif#frame-{{$frame_id}}" method="POST">
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">フォーム名 <span class="badge badge-danger">必須</span></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="forms_name" value="{{old('forms_name', $form->forms_name)}}" class="form-control">
            @include('plugins.common.errors_inline', ['name' => 'forms_name'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">フォームモード</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                @foreach (FormMode::getMembers() as $enum_value => $enum_label)
                    <div class="custom-control custom-radio custom-control-inline">
                        @php $form_mode = $form->form_mode ?? FormMode::getDefault(); @endphp
                        @if (old('form_mode', $form_mode) == $enum_value)
                            <input type="radio" value="{{$enum_value}}" id="form_mode_{{$enum_value}}" name="form_mode" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="{{$enum_value}}" id="form_mode_{{$enum_value}}" name="form_mode" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="form_mode_{{$enum_value}}" id="label_form_mode_{{$enum_value}}">{{$enum_label}}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">データ保存</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="data_save_flag" value="0">
                @if ($create_flag)
                    {{-- 新規登録の場合は「データ保存」はonにしておく（データ保存設定の忘れ防止。基本は保存する。外したい時は外せるように。） --}}
                    <input type="checkbox" name="data_save_flag" value="1" class="custom-control-input" id="data_save_flag" @if(old('data_save_flag', '1')) checked=checked @endif>
                @else
                    <input type="checkbox" name="data_save_flag" value="1" class="custom-control-input" id="data_save_flag" @if(old('data_save_flag', $form->data_save_flag)) checked=checked @endif>
                @endif
                <label class="custom-control-label" for="data_save_flag">データを保存する（チェックを外すと、サイト上にデータを保持しません）</label>
            </div>
            @include('plugins.common.errors_inline', ['name' => 'data_save_flag'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">閲覧制限</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                @foreach (FormAccessLimitType::getMembers() as $enum_value => $enum_label)
                    <div class="custom-control custom-radio custom-control-inline">
                        @php
                            $access_limit_type = $form->access_limit_type ?? FormAccessLimitType::getDefault();
                            $checked = '';
                            if (old('access_limit_type', $access_limit_type) == $enum_value) {
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        @switch($enum_value)
                            @case(FormAccessLimitType::none)
                            @case(FormAccessLimitType::captcha)
                            @case(FormAccessLimitType::captcha_form_submit)
                                <input type="radio" value="{{$enum_value}}" id="access_limit_type_{{$enum_value}}" name="access_limit_type" class="custom-control-input" {{$checked}}
                                    data-toggle="collapse" data-target="#collapse_form_password{{$frame_id}}.show">
                                @break
                            @case(FormAccessLimitType::password)
                                <input type="radio" value="{{$enum_value}}" id="access_limit_type_{{$enum_value}}" name="access_limit_type" class="custom-control-input" {{$checked}}
                                    data-toggle="collapse" data-target="#collapse_form_password{{$frame_id}}:not(.show)" aria-expanded="true" aria-controls="collapse_form_password{{$frame_id}}">
                                @break
                        @endswitch
                        <label class="custom-control-label" for="access_limit_type_{{$enum_value}}" id="label_access_limit_type_{{$enum_value}}">{{$enum_label}}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="form-group row collapse" id="collapse_form_password{{$frame_id}}">
        <label class="{{$frame->getSettingLabelClass()}}">閲覧パスワード</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="form_password" value="{{old('form_password', $form->form_password)}}" class="form-control">
            @include('plugins.common.errors_inline', ['name' => 'form_password'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">仮登録数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            {{$form->tmp_entry_count}}
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">本登録数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            {{$form->active_entry_count}}
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">集計結果</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label>以下の権限は集計結果を表示できる</label>
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="can_view_inputs_moderator" value="0">
                <input type="checkbox" name="can_view_inputs_moderator" value="1" class="custom-control-input" id="can_view_inputs_moderator" @if(old('can_view_inputs_moderator', $form->can_view_inputs_moderator)) checked=checked @endif>
                <label class="custom-control-label" for="can_view_inputs_moderator">モデレータ</label>
            </div>
            <small class="text-muted">
                ※ 集計結果は、登録一覧と同じ内容を確認できます。<br />
                ※ チェックONにすると、表側に集計結果ボタンを表示します。
            </small>
        </div>
    </div>

    <div class="form-group row" id="div_entry_limit">
        <label class="{{$frame->getSettingLabelClass()}}">登録制限数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="entry_limit" value="{{old('entry_limit', $form->entry_limit)}}" class="form-control">
            @include('plugins.common.errors_inline', ['name' => 'entry_limit'])
            <small class="text-muted">
                ※ 未入力か 0 の場合、登録数を制限しません。<br>
                ※ 制限する場合、本登録数で制限します。
            </small>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">登録制限越えのメッセージ</label>
            <textarea name="entry_limit_over_message" class="form-control" rows=5 placeholder="（例）制限数に達したため登録を終了しました。">{{old('entry_limit_over_message', $form->entry_limit_over_message)}}</textarea>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">表示期間</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                <label>表示期間の制御</label><br>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="0" id="display_control_flag_0" name="display_control_flag" class="custom-control-input" @if(old('display_control_flag', $form->display_control_flag) == 0) checked="checked" @endif data-toggle="collapse" data-target="#collapse_display_control{{$frame_id}}.show">
                    <label class="custom-control-label" for="display_control_flag_0">表示期間で制御しない</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="1" id="display_control_flag_1" name="display_control_flag" class="custom-control-input" @if(old('display_control_flag', $form->display_control_flag) == 1) checked="checked" @endif data-toggle="collapse" data-target="#collapse_display_control{{$frame_id}}:not(.show)" aria-expanded="true" aria-controls="collapse_display_control{{$frame_id}}">
                    <label class="custom-control-label" for="display_control_flag_1">表示期間で制御する</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row collapse" id="collapse_display_control{{$frame_id}}">
        <label class="{{$frame->getSettingLabelClass()}} pt-0"></label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                <label>表示開始日時</label>
                <div class="input-group" id="display_from{{$frame_id}}" data-target-input="nearest">
                    <input class="form-control datetimepicker-input" type="text" name="display_from" value="{{old('display_from', $form->display_from)}}" data-target="#display_from{{$frame_id}}">
                    <div class="input-group-append" data-target="#display_from{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                @include('plugins.common.errors_inline', ['name' => 'display_from'])
                <small class="text-muted">
                    ※ 未入力の場合、開始日時で表示制限しません。<br>
                    ※ 開始日時になった瞬間に公開します。例えば14:00の場合、14:00に公開します。<br>
                    <br><!-- 項目が縦中央によるため、改行でそろえる -->
                </small>
            </div>
            <div class="col pl-0">
                <label>表示終了日時</label>
                <div class="input-group" id="display_to{{$frame_id}}" data-target-input="nearest">
                    <input class="form-control datetimepicker-input" type="text" name="display_to" value="{{old('display_to', $form->display_to)}}" data-target="#display_to{{$frame_id}}">
                    <div class="input-group-append" data-target="#display_to{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                @include('plugins.common.errors_inline', ['name' => 'display_to'])
                <small class="text-muted">
                    ※ 未入力の場合、終了日時で表示制限しません。<br>
                    ※ 終了日時になった瞬間に表示終了します。例えば15:00の場合、14:59まで表示します。
                </small>
            </div>
            {{-- DateTimePicker 呼び出し --}}
            @include('plugins.common.datetimepicker', ['element_id' => "display_from{$frame_id}", 'side_by_side' => true])
            @include('plugins.common.datetimepicker', ['element_id' => "display_to{$frame_id}", 'side_by_side' => true])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">登録期間</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                <label>登録期間の制御</label><br>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="0" id="regist_control_flag_0" name="regist_control_flag" class="custom-control-input" @if(old('regist_control_flag', $form->regist_control_flag) == 0) checked="checked" @endif data-toggle="collapse" data-target="#collapse_regist_control{{$frame_id}}.show">
                    <label class="custom-control-label" for="regist_control_flag_0">登録期間で制御しない</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="1" id="regist_control_flag_1" name="regist_control_flag" class="custom-control-input" @if(old('regist_control_flag', $form->regist_control_flag) == 1) checked="checked" @endif data-toggle="collapse" data-target="#collapse_regist_control{{$frame_id}}:not(.show)" aria-expanded="true" aria-controls="collapse_regist_control{{$frame_id}}">
                    <label class="custom-control-label" for="regist_control_flag_1">登録期間で制御する</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row collapse" id="collapse_regist_control{{$frame_id}}">
        <label class="{{$frame->getSettingLabelClass()}} pt-0"></label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                <label>登録開始日時</label>
                <div class="input-group" id="regist_from{{$frame_id}}" data-target-input="nearest">
                    <input class="form-control datetimepicker-input" type="text" name="regist_from" value="{{old('regist_from', $form->regist_from)}}" data-target="#regist_from{{$frame_id}}">
                    <div class="input-group-append" data-target="#regist_from{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                @include('plugins.common.errors_inline', ['name' => 'regist_from'])
                <small class="text-muted">
                    ※ 未入力の場合、開始日時で登録制限しません。<br>
                    ※ 開始日時になった瞬間に登録開始します。例えば14:00の場合、14:00に登録開始します。
                </small>
            </div>
            <div class="col pl-0">
                <label>登録終了日時</label>
                <div class="input-group" id="regist_to{{$frame_id}}" data-target-input="nearest">
                    <input class="form-control datetimepicker-input" type="text" name="regist_to" value="{{old('regist_to', $form->regist_to)}}" data-target="#regist_to{{$frame_id}}">
                    <div class="input-group-append" data-target="#regist_to{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                @include('plugins.common.errors_inline', ['name' => 'regist_to'])
                <small class="text-muted">
                    ※ 未入力の場合、終了日時で登録制限しません。<br>
                    ※ 終了日時になった瞬間に登録終了します。例えば15:00の場合、14:59まで登録できます。
                </small>
            </div>
            {{-- DateTimePicker 呼び出し --}}
            @include('plugins.common.datetimepicker', ['element_id' => "regist_from{$frame_id}", 'side_by_side' => true])
            @include('plugins.common.datetimepicker', ['element_id' => "regist_to{$frame_id}", 'side_by_side' => true])
        </div>
    </div>

    <div class="row">
        <label class="{{$frame->getSettingLabelClass()}}">メール送信先</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="mail_send_flag" value="0">
                <input type="checkbox" name="mail_send_flag" value="1" class="custom-control-input" id="mail_send_flag" @if(old('mail_send_flag', $form->mail_send_flag)) checked=checked @endif>
                <label class="custom-control-label" for="mail_send_flag">以下のアドレスにメール送信する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">送信するメールアドレス（複数ある場合はカンマで区切る）</label>
            <input type="text" name="mail_send_address" value="{{old('mail_send_address', $form->mail_send_address)}}" class="form-control">
            @include('plugins.common.errors_inline', ['name' => 'mail_send_address'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="user_mail_send_flag" value="0">
                <input type="checkbox" name="user_mail_send_flag" value="1" class="custom-control-input" id="user_mail_send_flag" @if(old('user_mail_send_flag', $form->user_mail_send_flag)) checked=checked @endif>
                <label class="custom-control-label" for="user_mail_send_flag">登録者にメール送信する</label>
            </div>
            @include('plugins.common.errors_inline', ['name' => 'user_mail_send_flag'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0"></label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">
                <label>メールの添付ファイル制御</label><br>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="0" id="mail_attach_flag_0" name="mail_attach_flag" class="custom-control-input" @if(old('mail_attach_flag', $form->mail_attach_flag) == 0) checked="checked" @endif>
                    <label class="custom-control-label" for="mail_attach_flag_0">メールにファイルを添付しない</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="1" id="mail_attach_flag_1" name="mail_attach_flag" class="custom-control-input" @if(old('mail_attach_flag', $form->mail_attach_flag) == 1) checked="checked" @endif>
                    <label class="custom-control-label" for="mail_attach_flag_1">メールにファイルを添付する</label>
                </div>
                <div>
                    <small class="text-muted">
                        ※ フォームにファイル型の項目があれば、メールにファイルを添付できます。<br>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">仮登録メール</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="use_temporary_regist_mail_flag" value="0">
                <input type="checkbox" name="use_temporary_regist_mail_flag" value="1" class="custom-control-input" id="use_temporary_regist_mail_flag" @if(old('use_temporary_regist_mail_flag', $form->use_temporary_regist_mail_flag)) checked=checked @endif data-toggle="collapse" data-target="#collapse_temporary_regist{{$frame_id}}" aria-expanded="false" aria-controls="collapse_temporary_regist{{$frame_id}}">
                <label class="custom-control-label" for="use_temporary_regist_mail_flag">登録者に仮登録メールを送信する</label>
            </div>
            <div>
                <small class="text-muted">
                    ※ 仮登録メールを使う事で、本登録前にメールアドレスの確認がとれます。<br>
                    ※ 仮登録メールを使うには、「データ保存」と「登録者にメール送信する」のチェックを付けてください。また「仮登録メールフォーマット」に [[entry_url]] を含めてください。
                </small>
            </div>
        </div>
    </div>

    <div class="collapse" id="collapse_temporary_regist{{$frame_id}}">
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <label class="control-label">仮登録メール件名</label>
                <input type="text" name="temporary_regist_mail_subject" value="{{old('temporary_regist_mail_subject', $form->temporary_regist_mail_subject)}}" class="form-control" placeholder="（例）仮登録のお知らせと本登録のお願い">
                <small class="text-muted">
                    ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                    ※ [[form_name]] を記述すると該当部分にフォーム名が入ります。<br>
                    ※ [[to_datetime]] を記述すると該当部分に登録日時が入ります。<br>
                </small>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <label class="control-label">仮登録メールフォーマット</label>
                <textarea name="temporary_regist_mail_format" class="form-control" rows=5 placeholder="（例）仮登録を受け付けました。&#13;&#10;引き続き、下記のURLへアクセスしていただき、本登録を行ってください。&#13;&#10;&#13;&#10;↓本登録URL&#13;&#10;[[entry_url]]&#13;&#10;&#13;&#10;※お使いのメールソフトによっては、URLが途中で切れてアクセスできない場合があります。&#13;&#10;　その場合はクリックされるのではなくURLをブラウザのアドレス欄にコピー＆ペーストしてアクセスしてください。&#13;&#10;----------------------------------&#13;&#10;[[body]]&#13;&#10;----------------------------------">{{old('temporary_regist_mail_format', $form->temporary_regist_mail_format)}}</textarea>
                @include('plugins.common.errors_inline', ['name' => 'temporary_regist_mail_format'])
                <small class="text-muted">
                    ※ [[entry_url]] を記述すると本登録URLが入ります。本登録URLの有効期限は仮登録後60分です。<br>
                    ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                    ※ [[form_name]] を記述すると該当部分にフォーム名が入ります。<br>
                    ※ [[to_datetime]] を記述すると該当部分に登録日時が入ります。<br>
                    ※ [[body]] を記述すると該当部分に登録内容が入ります。
                </small>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">仮登録後のメッセージ</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea name="temporary_regist_after_message" class="form-control" rows=5 placeholder="（例）仮登録を受け付けました。&#13;&#10;メールを送信しましたので内容をご確認の上、本登録を行ってください。">{{old('temporary_regist_after_message', $form->temporary_regist_after_message)}}</textarea>
            </div>
        </div>
    </div>

{{--
    <div class="form-group">
        <label class="control-label">From メール送信者名</label>
        <input type="text" name="from_mail_name" value="{{old('from_mail_name', $form->from_mail_name)}}" class="form-control">
    </div>
--}}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">本登録メール</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">本登録メール件名</label>
            <input type="text" name="mail_subject" value="{{old('mail_subject', $form->mail_subject)}}" class="form-control">
            <small class="text-muted">
                ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                ※ [[form_name]] を記述すると該当部分にフォーム名が入ります。<br>
                ※ [[to_datetime]] を記述すると該当部分に登録日時が入ります。<br>
                ※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）
            </small>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">本登録メールフォーマット</label>
            <textarea name="mail_format" class="form-control" rows=5 placeholder="（例）受付内容をお知らせいたします。&#13;&#10;----------------------------------&#13;&#10;[[body]]&#13;&#10;----------------------------------">{{old('mail_format', $form->mail_format)}}</textarea>
            <small class="text-muted">
                ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                ※ [[form_name]] を記述すると該当部分にフォーム名が入ります。<br>
                ※ [[to_datetime]] を記述すると該当部分に登録日時が入ります。<br>
                ※ [[body]] を記述すると該当部分に登録内容が入ります。<br>
                ※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）
            </small>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">本登録後のメッセージ <span class="badge badge-danger">必須</span></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="after_message" class="form-control" rows=5 placeholder="（例）お申込みありがとうございます。&#13;&#10;受付番号は[[number]]になります。">{{old('after_message', $form->after_message)}}</textarea>
            @include('plugins.common.errors_inline', ['name' => 'after_message'])
            <small class="text-muted">
                ※ HTMLでも記述できます。<br />
                ※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）
            </small>
        </div>
    </div>

    <hr>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">採番</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="numbering_use_flag" value="0">
                <input type="checkbox" name="numbering_use_flag" value="1" class="custom-control-input" id="numbering_use_flag" @if(old('numbering_use_flag', $form->numbering_use_flag)) checked=checked @endif data-toggle="collapse" data-target="#app_numbering_prefix_{{$frame_id}}" aria-expanded="false" aria-controls="app_numbering_prefix_{{$frame_id}}">
                <label class="custom-control-label" for="numbering_use_flag">採番機能を使用する</label>
            </div>
        </div>
    </div>

    <div id="app_numbering_prefix_{{ $frame->id }}" class="form-group row collapse">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">採番プレフィックス</label>
            <input type="text" id="numbering_prefix" name="numbering_prefix" value="{{old('numbering_prefix', $form->numbering_prefix)}}" class="form-control" v-model="v_numbering_prefix">
            <small class="text-muted">
                ※ 採番イメージ：@{{ v_numbering_prefix + '000001' }}<br>
                ※ 初回採番後のデータは<a href="{{ url('/manage/number') }}" target="_blank">管理画面</a>から確認できます。<br>
                ※ 採番機能の使用時は<a href="{{ url("/plugin/forms/listInputs/{$page->id}/{$frame_id}#frame-{$frame_id}") }}" target="_blank">登録一覧</a>からも採番値が確認できます。
            </small>
        </div>
    </div>

    <hr>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">他プラグイン連携</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="other_plugins_register_use_flag" value="0">
                <input type="checkbox" name="other_plugins_register_use_flag" value="1" class="custom-control-input" id="other_plugins_register_use_flag" @if(old('other_plugins_register_use_flag', $form->other_plugins_register_use_flag)) checked=checked @endif data-toggle="collapse" data-target="#collapse_other_plugins{{$frame_id}}" aria-expanded="false" aria-controls="collapse_other_plugins{{$frame_id}}">
                <label class="custom-control-label" for="other_plugins_register_use_flag">他プラグイン連携機能を使用する</label>
            </div>
        </div>
    </div>

    <div class="form-group row collapse" id="collapse_other_plugins{{$frame_id}}">
        <label class="{{$frame->getSettingLabelClass()}}">対象ページ - フレーム</label>
        <div class="{{$frame->getSettingInputClass(false, true)}}">
            <ul class="nav nav-pills" role="tablist">
                @foreach(FormsRegisterTargetPlugin::getPluginsCanSpecifiedFrames() as $target_plugin => $target_plugin_full)
                    <li class="nav-item">
                        <a href="#{{$target_plugin}}{{$frame->id}}" class="nav-link @if($loop->first) active @endif" data-toggle="tab" role="tab">{{$target_plugin_full}}</a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach(FormsRegisterTargetPlugin::getPluginsCanSpecifiedFrames() as $target_plugin => $target_plugin_full)
                    <div id="{{$target_plugin}}{{$frame->id}}" class="tab-pane card @if($loop->first) active @endif" role="tabpanel">
                        <div class="card-body py-2 pl-3">
                            @foreach($target_plugins_frames as $target_plugins_frame)
                                @if ($target_plugins_frame->plugin_name == $target_plugin)
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="target_frame_ids[{{$target_plugins_frame->id}}]" value="{{$target_plugins_frame->id}}" class="custom-control-input" id="target_plugins_frame_{{$target_plugins_frame->id}}" @if(old("target_frame_ids.$target_plugins_frame->id", $form->isTargetFrame($target_plugins_frame->id))) checked=checked @endif>
                                        <label class="custom-control-label" for="target_plugins_frame_{{$target_plugins_frame->id}}">{{$target_plugins_frame->page_name}} - {{$target_plugins_frame->frame_title}}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @include('plugins.common.errors_inline', ['name' => 'target_plugins_frames'])
            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($form) || $create_flag)
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存フォームの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$form_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$form_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">フォームを削除します。<br>このフォームに登録された内容も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/forms/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame_id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
<script>
    createApp({
      data: function() {
        return {
          v_numbering_prefix: '{{old('numbering_prefix', $form->numbering_prefix)}}'
        }
      }
    }).mount('#app_numbering_prefix_{{ $frame->id }}');

    {{-- 初期状態で開くもの --}}
    @php $access_limit_type = $form->access_limit_type ?? FormAccessLimitType::getDefault(); @endphp
    @if (old('access_limit_type', $access_limit_type) == FormAccessLimitType::password)
        // 閲覧パスワード
        $('#collapse_form_password{{$frame_id}}').collapse('show')
    @endif

    @if(old('display_control_flag', $form->display_control_flag) == 1)
        // 表示期間
        $('#collapse_display_control{{$frame_id}}').collapse('show')
    @endif

    @if(old('regist_control_flag', $form->regist_control_flag) == 1)
        // 登録期間
        $('#collapse_regist_control{{$frame_id}}').collapse('show')
    @endif

    @if(old('use_temporary_regist_mail_flag', $form->use_temporary_regist_mail_flag))
        // 仮登録メール
        $('#collapse_temporary_regist{{$frame_id}}').collapse('show')
    @endif

    @if (old('numbering_use_flag', $form->numbering_use_flag))
        // 採番プレフィックス
        $('#app_numbering_prefix_{{$frame_id}}').collapse('show')
    @endif

    @if(old('other_plugins_register_use_flag', $form->other_plugins_register_use_flag))
        // 対象ページ - フレーム
        $('#collapse_other_plugins{{$frame_id}}').collapse('show')
    @endif
</script>
@endif
@endsection
