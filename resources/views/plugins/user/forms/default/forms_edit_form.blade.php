{{--
 * フォーム編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$form->id && !$create_flag)
    <div class="alert alert-warning mt-2">
        <i class="fas fa-exclamation-circle"></i>
        フォーム選択画面から選択するか、フォーム新規作成で作成してください。
    </div>
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

{{-- create_flag がtrue の場合、新規作成するためにforms_id を空にする --}}
@if ($create_flag)
<form action="{{url('/')}}/plugin/forms/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
@else
<form action="{{url('/')}}/plugin/forms/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame_id}}" method="POST" class="">
@endif
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">フォーム名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="forms_name" value="{{old('forms_name', $form->forms_name)}}" class="form-control">
            @if ($errors && $errors->has('forms_name')) <div class="text-danger">{{$errors->first('forms_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">登録制限数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="entry_limit" value="{{old('entry_limit', $form->entry_limit)}}" class="form-control">
            <small class="text-muted">※ 未入力か 0 の場合、登録数を制限しません。</small><br>
            @if ($errors && $errors->has('entry_limit')) <div class="text-danger">{{$errors->first('entry_limit')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メール送信先</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
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
            @if ($errors && $errors->has('mail_send_address')) <div class="text-danger">{{$errors->first('mail_send_address')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="user_mail_send_flag" value="1" class="custom-control-input" id="user_mail_send_flag" @if(old('user_mail_send_flag', $form->user_mail_send_flag)) checked=checked @endif>
                <label class="custom-control-label" for="user_mail_send_flag">登録者にメール送信する</label>
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
        <label class="{{$frame->getSettingLabelClass()}}">メール件名</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="mail_subject" value="{{old('mail_subject', $form->mail_subject)}}" class="form-control">
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メールフォーマット</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="mail_format" class="form-control" rows=5 placeholder="（例）受付内容をお知らせいたします。&#13;&#10;----------------------------------&#13;&#10;[[body]]&#13;&#10;----------------------------------">{{old('mail_format', $form->mail_format)}}</textarea>
            <small class="text-muted">※ [[site_name]] を記述すると該当部分にサイト名が入ります。</small><br>
            <small class="text-muted">※ [[body]] を記述すると該当部分に登録内容が入ります。</small><br>
            <small class="text-muted">※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">データ保存</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="data_save_flag" value="1" class="custom-control-input" id="data_save_flag" @if(old('data_save_flag', $form->data_save_flag)) checked=checked @endif>
                <label class="custom-control-label" for="data_save_flag">データを保存する（チェックを外すと、サイト上にデータを保持しません）</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">登録後のメッセージ</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="after_message" class="form-control" rows=5 placeholder="（例）お申込みありがとうございます。&#13;&#10;受付番号は[[number]]になります。">{{old('after_message', $form->after_message)}}</textarea>
            <small class="text-muted">※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）</small>
        </div>
    </div>

    <hr>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">採番</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="numbering_use_flag" value="1" class="custom-control-input" id="numbering_use_flag" @if(old('numbering_use_flag', $form->numbering_use_flag)) checked=checked @endif>
                <label class="custom-control-label" for="numbering_use_flag">採番機能を使用する</label>
            </div>
        </div>
    </div>

    <div id="app_{{ $frame->id }}" class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">採番プレフィックス</label>
            <input type="text" id="numbering_prefix" name="numbering_prefix" value="{{old('numbering_prefix', $form->numbering_prefix)}}" class="form-control" v-model="v_numbering_prefix">
            <small class="text-muted">※ 採番イメージ：@{{ v_numbering_prefix + '000001' }}</small><br>
            <small class="text-muted">※ 初回採番後のデータは<a href="{{ url('/manage/number') }}" target="_blank">管理画面</a>から確認できます。</small>
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
    new Vue({
      el: "#app_{{ $frame->id }}",
      data: {
        v_numbering_prefix: document.getElementById('numbering_prefix').value
      }
    })
</script>
@endif
@endsection
