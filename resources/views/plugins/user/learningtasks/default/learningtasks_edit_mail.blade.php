{{--
 * 課題管理・メール設定画面テンプレート。
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

{{-- メール設定フォーム --}}
@if (empty($learningtask) || !$learningtask->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する課題管理を選択するか、作成してください。
    </div>
@else
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveMail/{{$page->id}}/{{$frame_id}}/{{$learningtask->id}}#frame-{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/editMail/{{$page->id}}/{{$frame_id}}/{{$learningtask->id}}#frame-{{$frame_id}}">

    <div class="alert alert-warning mt-2">
        <i class="fas fa-exclamation-circle"></i>
        各設定で以下の埋め込みキーワードが使用できます。<br />
        [[post_title]] = 課題タイトル<br />
        [[student_name]] = 受講者ユーザー名<br />
        [[teacher_name]] = 教員ユーザー名<br />
    </div>

    <h5><span class="badge badge-secondary">メール件名</span></h5>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">レポートの課題提出</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[1]" value="{{old('subjects.1', $tool->getMailConfig('subject', 1))}}" class="form-control" placeholder="空の場合は「レポートが提出されました。」">
            @if ($errors && $errors->has('subjects.1')) <div class="text-danger">{{$errors->first('subjects.1')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">レポートの評価登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[2]" value="{{old('subjects.2', $tool->getMailConfig('subject', 2))}}" class="form-control" placeholder="空の場合は「レポートの評価が登録されました。」">
            @if ($errors && $errors->has('subjects.2')) <div class="text-danger">{{$errors->first('subjects.2')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">レポートコメント登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[3]" value="{{old('subjects.3', $tool->getMailConfig('subject', 3))}}" class="form-control" placeholder="空の場合は「レポートにコメントが登録されました。」">
            @if ($errors && $errors->has('subjects.3')) <div class="text-danger">{{$errors->first('subjects.3')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">試験の解答提出</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[5]" value="{{old('subjects.5', $tool->getMailConfig('subject', 5))}}" class="form-control" placeholder="空の場合は「試験の解答が提出されました。」">
            @if ($errors && $errors->has('subjects.5')) <div class="text-danger">{{$errors->first('subjects.5')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">試験の評価登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[6]" value="{{old('subjects.6', $tool->getMailConfig('subject', 6))}}" class="form-control" placeholder="空の場合は「試験の評価が登録されました。」">
            @if ($errors && $errors->has('subjects.6')) <div class="text-danger">{{$errors->first('subjects.6')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">試験のコメント登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[7]" value="{{old('subjects.7', $tool->getMailConfig('subject', 7))}}" class="form-control" placeholder="空の場合は「試験のコメントが登録されました。」">
            @if ($errors && $errors->has('subjects.7')) <div class="text-danger">{{$errors->first('subjects.7')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">総合評価の登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="subjects[8]" value="{{old('subjects.8', $tool->getMailConfig('subject', 8))}}" class="form-control" placeholder="空の場合は「総合評価が登録されました。」">
            @if ($errors && $errors->has('subjects.8')) <div class="text-danger">{{$errors->first('subjects.8')}}</div> @endif
        </div>
    </div>

    <h5><span class="badge badge-secondary">メール本文</span></h5>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">レポートの課題提出</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[1]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」のレポートが提出されました。&#13;&#10;評価をお願いします。">{{old('bodys.1', $tool->getMailConfig('body', 1))}}</textarea>
            @if ($errors && $errors->has('bodys.1')) <div class="text-danger">{{$errors->first('body.1')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">レポートの評価登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[2]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」のレポートの評価が登録されました。&#13;&#10;確認をお願いします。">{{old('bodys.2', $tool->getMailConfig('body', 2))}}</textarea>
            @if ($errors && $errors->has('bodys.2')) <div class="text-danger">{{$errors->first('body.2')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">レポートコメント登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[3]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」にコメントが登録されました。&#13;&#10;確認をお願いします。">{{old('bodys.3', $tool->getMailConfig('body', 3))}}</textarea>
            @if ($errors && $errors->has('bodys.3')) <div class="text-danger">{{$errors->first('body.3')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">試験の解答提出</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[5]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」に試験の解答が提出されました。&#13;&#10;評価をお願いします。">{{old('bodys.5', $tool->getMailConfig('body', 5))}}</textarea>
            @if ($errors && $errors->has('bodys.5')) <div class="text-danger">{{$errors->first('body.5')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">試験の評価登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[6]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」の試験の評価が登録されました。&#13;&#10;確認をお願いします。">{{old('bodys.6', $tool->getMailConfig('body', 6))}}</textarea>
            @if ($errors && $errors->has('bodys.6')) <div class="text-danger">{{$errors->first('body.6')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">試験のコメント登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[7]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」に試験のコメントが登録されました。&#13;&#10;確認をお願いします。">{{old('bodys.7', $tool->getMailConfig('body', 7))}}</textarea>
            @if ($errors && $errors->has('bodys.7')) <div class="text-danger">{{$errors->first('body.7')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">総合評価の登録</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="bodys[8]" class="form-control" rows=5 placeholder="空の場合は以下の内容&#13;&#10;「[[post_title]]」の総合評価が登録されました。&#13;&#10;確認をお願いします。">{{old('bodys.8', $tool->getMailConfig('body', 8))}}</textarea>
            @if ($errors && $errors->has('bodys.8')) <div class="text-danger">{{$errors->first('body.8')}}</div> @endif
        </div>
    </div>

    <h5><span class="badge badge-secondary">メールフッター</span></h5>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">フッター（共通）</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <textarea name="footer" class="form-control" rows=5 placeholder="">{{old('footer', $tool->getMailConfig('footer', 0))}}</textarea>
            @if ($errors && $errors->has('footer')) <div class="text-danger">{{$errors->first('footer')}}</div> @endif
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($learningtask->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2"  onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span> キャンセル</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
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
