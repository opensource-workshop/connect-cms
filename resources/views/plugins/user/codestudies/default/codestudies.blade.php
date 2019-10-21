{{--
 * コードスタディ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コードスタディプラグイン
 --}}

<script type="text/javascript">
    {{-- 実行のsubmit JavaScript --}}
    function submit_codestudies_run() {
        form_codestudies.codestudies_run = "1";
        @if ($codestudy->id)
            form_codestudies.action = "/plugin/codestudies/run/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}";
        @else
            form_codestudies.action = "/plugin/codestudies/run/{{$page->id}}/{{$frame_id}}";
        @endif
        form_codestudies.submit();
    }
</script>

{{-- 結果があれば表示 --}}
@if (isset($run_check_msgs) && $run_check_msgs)
<div class="panel panel-danger">
    <div class="panel-heading">制限エラー</div>
    <div class="panel-body">
        @foreach ($run_check_msgs as $run_check_msg)
            {!!$run_check_msg!!}<br />
        @endforeach
    </div>
</div>
@endif

@if (isset($result) && $result)
@if ($error_flag == 1)
<div class="card border-danger">
@else
<div class="card border-primary">
@endif
    <div class="card-header">実行結果</div>
    <div class="card-body">
        @foreach ($result as $result_row)
            @if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                {!!mb_convert_encoding($result_row, 'UTF-8', 'SJIS-win')!!}<br />
            @else
                {!!$result_row!!}<br />
            @endif
        @endforeach
    </div>
</div>
@endif

@if ($codestudy->id)
    <form action="/plugin/codestudies/save/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}" method="POST" name="form_codestudies" class="">
@else
    <form action="/plugin/codestudies/save/{{$page->id}}/{{$frame_id}}" method="POST" name="form_codestudies" class="">
@endif

    {{ csrf_field() }}

    <div class="form-group">
        <label class="control-label">タイトル</label><br />
        <input type="text" name="title" value="{{old('title', $codestudy->title)}}" class="form-control">
    </div>

    <div class="form-group">
        <label class="control-label">コード <label class="badge badge-danger">必須</label></label><br />
        <textarea class="form-control" rows="10" name="code_text" style="font-family:'ＭＳ ゴシック', 'MS Gothic', 'Osaka－等幅', Osaka-mono, monospace;">{!!old('code_text', $codestudy->code_text)!!}</textarea>
        @if ($errors && $errors->has('code_text')) <div class="text-danger">{{$errors->first('code_text')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">言語 <label class="badge badge-danger">必須</span></label><br />
        <div class="card">
            <div class="card-body p-2">
                @if ($codestudy->study_lang == 'java' || old('study_lang') == 'java')
                    <label class="m-0"><input name="study_lang" type="radio" value="java" checked> Java</input></label>
                @else
                    <label class="m-0"><input name="study_lang" type="radio" value="java"> Java</input></label>
                @endif
                @if ($codestudy->study_lang == 'php' || old('study_lang') == 'php')
                    <label class="m-0"><input name="study_lang" type="radio" value="php" checked> PHP</input></label>
                @else
                    <label class="m-0"><input name="study_lang" type="radio" value="php"> PHP</input></label>
                @endif
            </div>
        </div>
        @if ($errors && $errors->has('study_lang')) <div class="text-danger">{{$errors->first('study_lang')}}</div> @endif
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-sm-2"></div>
            <div class="col-sm-8 mx-auto">
                <div class="text-center">
                    <button type="submit" class="btn btn-success mr-3"><i class="far fa-save"></i> 保存のみ</button>
                    <button type="button" class="btn btn-primary mr-3" onclick="javascript:submit_codestudies_run();"><i class="fas fa-check"></i> 保存と実行</button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
                </div>
            </div>
            <div class="col-sm-2">
                @if (!empty($codestudy->id))
                    <a data-toggle="collapse" href="#collapse{{$codestudy->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">削除</span></span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>

<div id="collapse{{$codestudy->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger mb-3">
        <div class="card-body">
            <span class="text-danger">プログラムを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/codestudies/delete/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('プログラムを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-info">
    <div class="card-header">保存済みプログラム</div>
    <div class="card-body">
        <ol>
        @foreach($codestudies as $codestudy)
            @if($codestudy->title)
                <li><a href="{{URL::to('/')}}/plugin/codestudies/edit/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}">{{$codestudy->title}}</a> [{{$codestudy->study_lang}}]</li>
            @else
                <li><a href="{{URL::to('/')}}/plugin/codestudies/edit/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}">無題</a> [{{$codestudy->study_lang}}]</li>
            @endif
        @endforeach
        </ol>
    </div>
</div>
