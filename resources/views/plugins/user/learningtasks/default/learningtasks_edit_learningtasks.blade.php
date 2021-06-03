{{--
 * 課題管理編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.learningtasks.learningtasks_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@include('common.errors_form_line')

<script>
    $(function () {
        /**
         * カレンダーボタン押下
         */
        $('#report_end_at{{$frame_id}}').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            dayViewHeaderFormat: 'YYYY MMM',
            sideBySide: true,
        });
    });
</script>

@if (!$learningtask->id && !$create_flag)
    {{-- idなし & 変更 = DB未選択&変更:初期表示 --}}
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> 設定画面から、使用する課題管理を選択するか、作成してください。
    </div>
@else
    @if (session('flash_message'))
        <div class="alert alert-success">
            <i class="fas fa-exclamation-circle"></i> {{ session('flash_message') }}
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i>
            @if (empty($learningtask) || $create_flag)
                {{-- 登録：初期表示 --}}
                新しい課題管理設定を登録します。
            @else
                {{-- 変更：初期表示 --}}
                課題管理設定を変更します。
            @endif
        </div>
    @endif
@endif

@if (empty($learningtask) || (!$learningtask->id && !$create_flag))
@else
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにlearningtask_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/createBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
        <input type="hidden" name="learningtask_id" value="">
        <input type="hidden" name="copy_learningtask_id" value="{{old('copy_learningtask_id', $learningtask->id)}}">
    @else
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/editBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
        <input type="hidden" name="learningtask_id" value="{{$learningtask->id}}">
        <input type="hidden" name="copy_learningtask_id" value="">
    @endif

    <h5><span class="badge badge-secondary">基本設定</span></h5>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">課題管理名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="learningtasks_name" value="{{old('learningtasks_name', $learningtask->learningtasks_name)}}" class="form-control @if ($errors && $errors->has('learningtasks_name')) border-danger @endif">
            @include('common.errors_inline', ['name' => 'learningtasks_name'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $learningtask->view_count)}}" class="form-control col-sm-3 @if ($errors && $errors->has('view_count')) border-danger @endif">
            @include('common.errors_inline', ['name' => 'view_count'])
        </div>
    </div>

    {{-- 課題管理にRSS が必要か、再考する。
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">RSS</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtask->rss == 1)
                    <input type="radio" value="1" id="rss_off" name="rss" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="rss_off" name="rss" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="rss_off">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtask->rss == 0)
                    <input type="radio" value="0" id="rss_on" name="rss" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="rss_on" name="rss" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="rss_on">表示しない</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">RSS件数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="rss_count" value="{{old('rss_count', isset($learningtask->rss_count) ? $learningtask->rss_count : 0)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('rss_count')) <div class="text-danger">{{$errors->first('rss_count')}}</div> @endif
        </div>
    </div>
    --}}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">順序条件</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("sequence_conditions", $learningtask->sequence_conditions) == 0)
                    <input type="radio" value="0" id="sequence_conditions_0" name="sequence_conditions" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="sequence_conditions_0" name="sequence_conditions" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="sequence_conditions_0">最新順</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("sequence_conditions", $learningtask->sequence_conditions) == 1)
                    <input type="radio" value="1" id="sequence_conditions_1" name="sequence_conditions" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="sequence_conditions_1" name="sequence_conditions" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="sequence_conditions_1">投稿順</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("sequence_conditions", $learningtask->sequence_conditions) == 2)
                    <input type="radio" value="2" id="sequence_conditions_2" name="sequence_conditions" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="2" id="sequence_conditions_2" name="sequence_conditions" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="sequence_conditions_2">指定順</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">参加設定</span></h5>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">ログインの要否</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("base_settings.use_need_auth", $tool->getFunction('use_need_auth')) == 'off')
                    <input type="radio" value="off" id="use_need_auth_0" name="base_settings[use_need_auth]" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="off" id="use_need_auth_0" name="base_settings[use_need_auth]" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_need_auth_0">非ログインでも閲覧可能</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("base_settings.use_need_auth", $tool->getFunction('use_need_auth')) == 'on')
                    <input type="radio" value="on" id="use_need_auth_1" name="base_settings[use_need_auth]" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="on" id="use_need_auth_1" name="base_settings[use_need_auth]" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_need_auth_1">閲覧にはログインが必要</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">レポート設定</span></h5>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">レポート提出機能</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report]" value="on" class="custom-control-input" id="use_report" @if(old("base_settings.use_report", $tool->getFunction('use_report')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report">提出</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_evaluate]" value="on" class="custom-control-input" id="use_report_evaluate" @if(old("base_settings.use_report_evaluate", $tool->getFunction('use_report_evaluate')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate">評価</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_reference]" value="on" class="custom-control-input" id="use_report_reference" @if(old("use_report_evaluate.use_report_reference", $tool->getFunction('use_report_reference')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_reference">教員から参考資料</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">提出</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_file]" value="on" class="custom-control-input" id="use_report_file" @if(old("base_settings.use_report_file", $tool->getFunction('use_report_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_comment]" value="on" class="custom-control-input" id="use_report_comment" @if(old("base_settings.use_report_comment", $tool->getFunction('use_report_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_comment">本文入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_report_mail]" value="on" class="custom-control-input" id="use_report_mail" @if(old("base_settings.use_report_mail", $tool->getFunction('use_report_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_mail">メール送信（教員宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">提出期限</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                @php
                    $name_function1 = "base_settings[".LearningtaskUseFunction::use_report_end."]";
                    $old_function1 = "base_settings.".LearningtaskUseFunction::use_report_end;
                    $id_function1 = LearningtaskUseFunction::use_report_end . $frame_id;
                @endphp

                {{-- チェック外した場合にも値を飛ばす対応 --}}
                <input type="hidden" value="0" name="{{$name_function1}}">

                <input type="checkbox"
                    name="{{$name_function1}}"
                    value="on"
                    class="custom-control-input"
                    id="{{$id_function1}}"
                    @if(old($old_function1, $tool->getFunction(LearningtaskUseFunction::use_report_end)) == 'on') checked=checked @endif
                >
                <label class="custom-control-label" for="{{$id_function1}}">以下の提出終了日時で制御する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label>提出終了日時</label>
            @php
                $name_function2 = "base_settings[".LearningtaskUseFunction::report_end_at."]";
                $old_function2 = "base_settings.".LearningtaskUseFunction::report_end_at;
                // idに.(ドット)を含むと、カレンダーピッカー動かなくなるため含めない
                $id_function2 = LearningtaskUseFunction::report_end_at . $frame_id;
            @endphp

            <div class="input-group col-md-6 pl-0" id="{{$id_function2}}" data-target-input="nearest">
                <input class="form-control datetimepicker-input @if ($errors && $errors->has($old_function2)) border-danger @endif"
                    type="text"
                    name="{{$name_function2}}"
                    value="{{old($old_function2, $tool->getFunction(LearningtaskUseFunction::report_end_at))}}"
                    data-target="#{{$id_function2}}"
                >
                <div class="input-group-append" data-target="#{{$id_function2}}" data-toggle="datetimepicker">
                    <div class="input-group-text @if ($errors && $errors->has($old_function2)) border-danger @endif">
                        <i class="far fa-clock"></i>
                    </div>
                </div>
            </div>
            @include('common.errors_inline', ['name' => $old_function2])
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">評価</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_evaluate_file]" value="on" class="custom-control-input" id="use_report_evaluate_file" @if(old("base_settings.use_report_evaluate_file", $tool->getFunction('use_report_evaluate_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_evaluate_comment]" value="on" class="custom-control-input" id="use_report_evaluate_comment" @if(old("base_settings.use_report_evaluate_comment", $tool->getFunction('use_report_evaluate_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_report_evaluate_mail]" value="on" class="custom-control-input" id="use_report_evaluate_mail" @if(old("base_settings.use_report_evaluate_mail", $tool->getFunction('use_report_evaluate_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_evaluate_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">教員から参考資料</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_reference_file]" value="on" class="custom-control-input" id="use_report_reference_file" @if(old("base_settings.use_report_reference_file", $tool->getFunction('use_report_reference_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_reference_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_reference_comment]" value="on" class="custom-control-input" id="use_report_reference_comment" @if(old("base_settings.use_report_reference_comment", $tool->getFunction('use_report_reference_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_reference_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_report_reference_mail]" value="on" class="custom-control-input" id="use_report_reference_mail" @if(old("base_settings.use_report_reference_mail", $tool->getFunction('use_report_reference_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_reference_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">表示方法</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_report_status_collapse]" value="on" class="custom-control-input" id="use_report_status_collapse" @if(old("base_settings.use_report_status_collapse", $tool->getFunction('use_report_status_collapse')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_report_status_collapse">履歴を開閉する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">試験設定</span></h5>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">レポート試験機能</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination]" value="on" class="custom-control-input" id="use_examination" @if(old("base_settings.use_examination", $tool->getFunction('use_examination')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination">提出</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_evaluate]" value="on" class="custom-control-input" id="use_examination_evaluate" @if(old("base_settings.use_examination_evaluate", $tool->getFunction('use_examination_evaluate')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate">評価</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_reference]" value="on" class="custom-control-input" id="use_examination_reference" @if(old("base_settings.use_examination_reference", $tool->getFunction('use_examination_reference')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_reference">教員から参考資料</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">提出</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_file]" value="on" class="custom-control-input" id="use_examination_file" @if(old("base_settings.use_examination_file", $tool->getFunction('use_examination_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_comment]" value="on" class="custom-control-input" id="use_examination_comment" @if(old("base_settings.use_examination_comment", $tool->getFunction('use_examination_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_comment">本文入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_examination_mail]" value="on" class="custom-control-input" id="use_examination_mail" @if(old("base_settings.use_examination_mail", $tool->getFunction('use_examination_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_mail">メール送信（教員宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">評価</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_evaluate_file]" value="on" class="custom-control-input" id="use_examination_evaluate_file" @if(old("base_settings.use_examination_evaluate_file", $tool->getFunction('use_examination_evaluate_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_evaluate_comment]" value="on" class="custom-control-input" id="use_examination_evaluate_comment" @if(old("base_settings.use_examination_evaluate_comment", $tool->getFunction('use_examination_evaluate_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_examination_evaluate_mail]" value="on" class="custom-control-input" id="use_examination_evaluate_mail" @if(old("base_settings.use_examination_evaluate_mail", $tool->getFunction('use_examination_evaluate_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_evaluate_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">教員から参考資料</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_reference_file]" value="on" class="custom-control-input" id="use_examination_reference_file" @if(old("base_settings.use_examination_reference_file", $tool->getFunction('use_examination_reference_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_reference_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_reference_comment]" value="on" class="custom-control-input" id="use_examination_reference_comment" @if(old("base_settings.use_examination_reference_comment", $tool->getFunction('use_examination_reference_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_reference_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_examination_reference_mail]" value="on" class="custom-control-input" id="use_examination_reference_mail" @if(old("base_settings.use_examination_reference_mail", $tool->getFunction('use_examination_reference_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_reference_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示方法</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_examination_status_collapse]" value="on" class="custom-control-input" id="use_examination_status_collapse" @if(old("base_settings.use_examination_status_collapse", $tool->getFunction('use_examination_status_collapse')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_examination_status_collapse">履歴を開閉する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">総合評価設定</span></h5>

    <div class="form-group row mb-0">
        <label class="{{$frame->getSettingLabelClass()}}">総合評価機能</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_evaluate]" value="on" class="custom-control-input" id="use_evaluate" @if(old("base_settings.use_evaluate", $tool->getFunction('use_evaluate')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate">評価</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">総合評価コメント</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_evaluate_file]" value="on" class="custom-control-input" id="use_evaluate_file" @if(old("base_settings.use_evaluate_file", $tool->getFunction('use_evaluate_file')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="base_settings[use_evaluate_comment]" value="on" class="custom-control-input" id="use_evaluate_comment" @if(old("base_settings.use_evaluate_comment", $tool->getFunction('use_evaluate_comment')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="base_settings[use_evaluate_mail]" value="on" class="custom-control-input" id="use_evaluate_mail" @if(old("base_settings.use_evaluate_mail", $tool->getFunction('use_evaluate_mail')) == 'on') checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($learningtask) || $create_flag)
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存課題管理の場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">課題管理を削除します。<br>この課題管理に記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/learningtasks/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$learningtask->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
