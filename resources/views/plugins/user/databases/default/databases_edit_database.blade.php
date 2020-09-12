{{--
 * データベース編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベースプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$database->id && !$create_flag)
    {{-- idなし & 変更 = DB未選択&変更:初期表示 --}}
    <div class="alert alert-warning mt-2">
        <i class="fas fa-exclamation-circle"></i>
        データベース選択画面から選択するか、データベース新規作成で作成してください。
    </div>
@else

    <div class="alert alert-info mt-2"><i class="fas fa-exclamation-circle"></i>

    @if ($message)
        {{-- 変更：変更確定後 --}}
        {{-- 登録：変更確定後 --}}
        {!!$message!!}
    @else
        @if (empty($database) || $create_flag)
            {{-- 登録：初期表示 --}}
            新しいデータベース設定を登録します。
        @else
            {{-- 変更：初期表示 --}}
            データベース設定を変更します。
        @endif
    @endif
    </div>
@endif

@if (!$database->id && !$create_flag)
@else

{{-- create_flag がtrue の場合、新規作成するためにdatabases_id を空にする --}}
@if ($create_flag)
<form action="{{url('/')}}/plugin/databases/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    <input type="hidden" name="copy_databases_id" value="{{old('copy_databases_id', $database->id)}}">
@else
<form action="{{url('/')}}/plugin/databases/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$database->id}}#frame-{{$frame_id}}" method="POST" class="">
    <input type="hidden" name="copy_databases_id" value="">
@endif
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">データベース名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="databases_name" value="{{old('databases_name', $database->databases_name)}}" class="form-control">
            @if ($errors && $errors->has('databases_name')) <div class="text-danger">{{$errors->first('databases_name')}}</div> @endif
        </div>
    </div>

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メール送信先</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="mail_send_flag" value="1" class="custom-control-input" id="mail_send_flag" @if(old('mail_send_flag', $database->mail_send_flag)) checked=checked @endif>
                <label class="custom-control-label" for="mail_send_flag">以下のアドレスにメール送信する</label>
            </div>
        </div>
    </div>
--}}
    <input type="hidden" name="mail_send_flag" value="0">

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">送信するメールアドレス（複数ある場合はカンマで区切る）</label>
            <input type="text" name="mail_send_address" value="{{old('mail_send_address', $database->mail_send_address)}}" class="form-control">
        </div>
    </div>
--}}
    <input type="hidden" name="mail_send_address" value="">

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="user_mail_send_flag" value="1" class="custom-control-input" id="user_mail_send_flag" @if(old('user_mail_send_flag', $database->user_mail_send_flag)) checked=checked @endif>
                <label class="custom-control-label" for="user_mail_send_flag">登録者にメール送信する</label>
            </div>
        </div>
    </div>
--}}
    <input type="hidden" name="user_mail_send_flag" value="">

{{--
    <div class="form-group">
        <label class="control-label">From メール送信者名</label>
        <input type="text" name="from_mail_name" value="{{old('from_mail_name', $database->from_mail_name)}}" class="form-control">
    </div>
--}}

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メール件名</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="mail_subject" value="{{old('mail_subject', $database->mail_subject)}}" class="form-control">
        </div>
    </div>
--}}
    <input type="hidden" name="mail_subject" value="">

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メールフォーマット</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="mail_databaseat" class="form-control" rows=5 placeholder="（例）受付内容をお知らせいたします。&#13;&#10;----------------------------------&#13;&#10;[[body]]&#13;&#10;----------------------------------">{{old('mail_databaseat', $database->mail_databaseat)}}</textarea>
            <small class="text-muted">※ [[body]] を記述すると該当部分に登録内容が入ります。</small><br>
            <small class="text-muted">※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）</small>
        </div>
    </div>
--}}
    <input type="hidden" name="mail_databaseat" value="">

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">データ保存</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="data_save_flag" value="1" class="custom-control-input" id="data_save_flag" @if(old('data_save_flag', $database->data_save_flag)) checked=checked @endif>
                <label class="custom-control-label" for="data_save_flag">データを保存する（チェックを外すと、サイト上にデータを保持しません）</label>
            </div>
        </div>
    </div>
--}}
    <input type="hidden" name="data_save_flag" value="1">

{{--
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">登録後のメッセージ</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="after_message" class="form-control" rows=5 placeholder="（例）お申込みありがとうございます。&#13;&#10;受付番号は[[number]]になります。">{{old('after_message', $database->after_message)}}</textarea>
            <small class="text-muted">※ [[number]] を記述すると該当部分に採番した番号が入ります。（採番機能の使用時）</small>
        </div>
    </div>
--}}
    <input type="hidden" name="after_message" value="1">

{{--
    <hr>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">採番</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="numbering_use_flag" value="1" class="custom-control-input" id="numbering_use_flag" @if(old('numbering_use_flag', $database->numbering_use_flag)) checked=checked @endif>
                <label class="custom-control-label" for="numbering_use_flag">採番機能を使用する</label>
            </div>
        </div>
    </div>

    <div id="app_{{ $frame->id }}" class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">採番プレフィックス</label>
            <input type="text" id="numbering_prefix" name="numbering_prefix" value="{{old('numbering_prefix', $database->numbering_prefix)}}" class="form-control" v-model="v_numbering_prefix">
            <small class="text-muted">※ 採番イメージ：@{{ v_numbering_prefix + '000001' }}</small><br>
            <small class="text-muted">※ 初回採番後のデータは<a href="{{ url('/manage/number') }}" target="_blank">管理画面</a>から確認できます。</small>
        </div>
    </div>
--}}
    <input type="hidden" name="numbering_use_flag" value="">
    <input type="hidden" name="numbering_prefix" value="">

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($database) || $create_flag)
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存データベースの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$database_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$database_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データベースを削除します。<br>このデータベースに登録された内容も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/databases/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$database_frame->databases_id}}" method="POST">
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
