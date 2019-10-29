{{--
 * フォーム編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

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
<form action="/plugin/forms/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにforms_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="forms_id" value="">
    @else
        <input type="hidden" name="forms_id" value="{{$form->id}}">
    @endif

    <div class="form-group">
        <label class="control-label">フォーム名 <label class="badge badge-danger">必須</span></label>
        <input type="text" name="forms_name" value="{{old('forms_name', $form->forms_name)}}" class="form-control">
        @if ($errors && $errors->has('forms_name')) <div class="text-danger">{{$errors->first('forms_name')}}</div> @endif
    </div>

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="mail_send_flag" value="1" class="custom-control-input" id="mail_send_flag" @if(old('mail_send_flag', $form->mail_send_flag)) checked=checked @endif>
            <label class="custom-control-label" for="mail_send_flag">以下のアドレスにメール送信する</label>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">送信するメールアドレス（複数ある場合はカンマで区切る）</label>
        <input type="text" name="mail_send_address" value="{{old('mail_send_address', $form->mail_send_address)}}" class="form-control">
    </div>

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="user_mail_send_flag" value="1" class="custom-control-input" id="user_mail_send_flag" @if(old('user_mail_send_flag', $form->user_mail_send_flag)) checked=checked @endif>
            <label class="custom-control-label" for="user_mail_send_flag">登録者にメール送信する</label>
        </div>
    </div>
{{--
    <div class="form-group">
        <label class="control-label">From メール送信者名</label>
        <input type="text" name="from_mail_name" value="{{old('from_mail_name', $form->from_mail_name)}}" class="form-control">
    </div>
--}}
    <div class="form-group">
        <label class="control-label">メール件名</label>
        <input type="text" name="mail_subject" value="{{old('mail_subject', $form->mail_subject)}}" class="form-control">
    </div>

    <div class="form-group">
        <label class="control-label">メールフォーマット　[[body]]　の部分に登録内容が入ります。</label>
        <textarea name="mail_format" class="form-control" rows=5>{{old('mail_format', $form->mail_format)}}</textarea>
    </div>

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="data_save_flag" value="1" class="custom-control-input" id="data_save_flag" @if(old('data_save_flag', $form->data_save_flag)) checked=checked @endif>
            <label class="custom-control-label" for="data_save_flag">データを保存する（チェックを外すと、サイト上にデータを保持しません）</label>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">登録後のメッセージ</label>
        <textarea name="after_message" class="form-control">{{old('after_message', $form->after_message)}}</textarea>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i></span> 
                @if (empty($form) || $create_flag)
                    登録確定
                @else
                    変更確定
                @endif
                </button>
            </div>

            {{-- 既存フォームの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$form_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="d-none">削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$form_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">フォームを削除します。<br>このフームに登録された内容も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/forms/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$form_frame->forms_id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
