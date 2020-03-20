{{--
 * フォームのサンプル画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
{{-- フォームの作成。フレームID を指定して、フレームを特定する --}}
@if (empty($id))
<form action="{{url('/')}}/plugin/sampleforms/confirm/{{$page->id}}/{{$frame_id}}" name="sampleforms_form" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
    <input name="id" type="hidden" value="">
@else
<form action="{{url('/')}}/plugin/sampleforms/confirm/{{$page->id}}/{{$frame_id}}/{{$id}}" name="sampleforms_form" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
    <input name="id" type="hidden" value="{{$id}}">
@endif

{{csrf_field()}}

{{-- テキスト --}}
<div class="form-group @if ($errors && $errors->has('column_text')) has-error @endif">
    <label class="control-label">テキスト <label class="badge badge-danger">必須</span></label>
    <input type="text" name="column_text" value="{{old('column_text', $sampleform->column_text)}}" class="form-control">
    @if ($errors && $errors->has('column_text')) <div class="text-danger">{{$errors->first('column_text')}}</div> @endif
</div>

{{-- ファイル --}}
<div class="form-group">
    <label class="control-label">ファイル</label>
    <input type="file" name="column_file" id="column_file" class="hide" onchange="$('#column_file_path').text($(this).val());">
    <div class="input-group">
        <span class="input-group-btn">
            <input type="button" value="参照" class="btn btn-primary" onclick="$('input[id=column_file]').click();">
        </span>
        <span id="column_file_path" class="form-control"></span>
    </div>

    @if ($sampleform->column_file)
    <div>
        <img src="{{url('/')}}/file/{{$sampleform->column_file}}" class="img-responsive thumbnail" style="margin-top: 10px;">
    </div>
    @endif
</div>

{{-- パスワード --}}
<div class="form-group">
    <label class="control-label">パスワード</label>
    <input type="password" name="column_password" value="{{old('column_password', $sampleform->column_password)}}" class="form-control">
</div>

{{-- テキストエリア --}}
<div class="form-group">
    <label class="control-label">テキストエリア</label>
    <textarea name="column_textarea" rows="5" class="form-control">{{str_replace('<br />', PHP_EOL, old('column_textarea', $sampleform->column_textarea))}}</textarea>
</div>

{{-- セレクト --}}
<div class="form-group">
    <div class="row">
        <div class="col-md-3">
            <label class="control-label">セレクト</label>
            <select name="column_select" class="form-control">
                <option value="">選択して下さい</option>
                <option value="選択肢1" @if(Input::old('column_select', $sampleform->column_select)=="選択肢1") selected @endif>選択肢1</option>
                <option value="選択肢2" @if(Input::old('column_select', $sampleform->column_select)=="選択肢2") selected @endif>選択肢2</option>
                <option value="選択肢3" @if(Input::old('column_select', $sampleform->column_select)=="選択肢3") selected @endif>選択肢3</option>
                <option value="選択肢4" @if(Input::old('column_select', $sampleform->column_select)=="選択肢4") selected @endif>選択肢4</option>
                <option value="選択肢5" @if(Input::old('column_select', $sampleform->column_select)=="選択肢5") selected @endif>選択肢5</option>
            </select>
        </div>
    </div>
</div>

{{-- チェックボックス --}}
<div class="form-group">
    <label class="control-label">チェックボックス</label>
    <div class="row">
    @for ($i = 1; $i < 5; $i++)
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-addon">
                    {{-- チェックボックスを生成する --}}
                    @include('plugins.user.sampleforms.default.form_checkbox', [
                        'checkbox_name' => 'column_checkbox[]',
                        'checkbox' => $sampleform->column_checkbox,
                        'check_value' => "選択肢$i"
                    ])
                </span>
                <span id="column_checkbox_{{$i}}" class="form-control" style="height: auto;">選択肢{{$i}}</span>
            </div>
        </div>
    @endfor
    </div>
</div>

{{-- ラジオボタン --}}
<div class="form-group">
    <label class="control-label">ラジオボタン</label>
    <div class="row">
    @for ($i = 1; $i < 5; $i++)
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-addon">
                    <input type="radio" name="column_radio" value="選択肢{{$i}}"@if(Input::old('column_radio', $sampleform->column_radio)=="選択肢{$i}") checked @endif>
                </span>
                <span id="column_checkbox_{{$i}}" class="form-control">選択肢{{$i}}</span>
            </div>
        </div>
    @endfor
    </div>
</div>

{{-- Submitボタン --}}
<div class="form-group text-center">
    <button type="submit" class="btn btn-primary form-horizontal">
        @if (empty($id))
            確認して登録
        @else
            確認して変更
        @endif
    </button>
    <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">キャンセル</button>
</div>

</form>
