{{--
 * フォームのサンプル画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
{{-- フォームの作成。フレームID を指定して、フレームを特定する --}}
@if (empty($id))
{{Form::open(['url' => $page->permanent_link . "?action=confirm&frame_id=$frame_id", 'files' => true])}}
@else
{{Form::open(['url' => $page->permanent_link . "?action=confirm&frame_id=$frame_id&id=$id", 'files' => true])}}
@endif

{{-- テキスト --}}
<div class="form-group">
    {{Form::label('name', 'テキスト')}}
    {{Form::text('column_text', $sampleform->column_text, ['class' => 'form-control'])}}
</div>

{{-- ファイル --}}
<div class="form-group">
    {{Form::label('name', 'ファイル')}}
    {{Form::file('column_file', ['id' => 'column_file', 'class' => 'hide', 'onchange' => "$('#column_file_path').text($(this).val())"])}}
    <div class="input-group">
        <span class="input-group-btn">
            {{Form::button('参照', ['class' => 'btn btn-primary', 'onclick' => "$('input[id=column_file]').click();"])}}
        </span>
        <span id="column_file_path" class="form-control"></span>
    </div>
{{$sampleform->column_file}}
</div>

{{-- パスワード --}}
<div class="form-group">
    {{Form::label('name', 'パスワード')}}
    {{Form::password('column_password', ['class' => 'form-control'])}}
</div>

{{-- テキストエリア --}}
<div class="form-group">
    {{Form::label('name', 'テキストエリア')}}
    {{Form::textarea('column_textarea', str_replace('<br />', PHP_EOL, $sampleform->column_textarea), ['class' => 'form-control', 'rows' => 5])}}
</div>

{{-- セレクト --}}
<div class="form-group">
    <div class="row">
        <div class="col-md-3">
            {{Form::label('name', 'セレクト')}}
            {{Form::select('column_select', ['選択肢1' => '選択肢1',
                                             '選択肢2' => '選択肢2',
                                             '選択肢3' => '選択肢3',
                                             '選択肢4' => '選択肢4',
                                             '選択肢5' => '選択肢5'],
                                             $sampleform->column_select, ['class' => 'form-control'])}}
        </div>
    </div>
</div>

{{-- チェックボックス --}}
<div class="form-group">
    {{Form::label('name', 'チェックボックス')}}
    <div class="row">
    @for ($i = 1; $i < 5; $i++)
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-addon">
                    {{-- チェックボックスを生成する --}}
                    @include('plugins.user.sampleforms.form_checkbox', [
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
    {{Form::label('name', 'ラジオボタン')}}
    <div class="row">
    @for ($i = 1; $i < 5; $i++)
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-addon">
                    {{Form::radio('column_radio', "選択肢$i", ($sampleform->column_radio == "選択肢$i" ? true : false ))}}
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
        フォーム保存
    </button>
    <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">キャンセル</button>
</div>

{{Form::close()}}

