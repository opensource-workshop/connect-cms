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
        form_codestudies.action = "/plugin/codestudies/run/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}";
        form_codestudies.submit();
    }
</script>

<form action="/plugin/codestudies/save/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}" method="POST" class="" name="form_codestudies">
    {{ csrf_field() }}
    <input name="codestudies_run" class="form-control" type="hidden" value="" />

    <div class="form-group">
        <label class="control-label">コード</label><br />
        <textarea class="form-control" rows="10" name="code_text">{!!old('code_text', $codestudy->code_text)!!}</textarea>
    </div>

    <div class="form-group">
        <div class="text-center">
            <label class="control-label">言語</label>
            <input name="study_lang" type="radio" value="java">Java</input>
            <input name="study_lang" type="radio" value="php" checked>PHP</input>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <div class="text-center">
                    <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 保存</button>
                    <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
                    <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="javascript:submit_codestudies_run();"><span class="glyphicon glyphicon-ok"></span> 実行</button>
                </div>
            </div>
        </div>
    </div>
</form>

<ul>
@foreach($codestudies as $codestudy)

    <li><a href="{{URL::to('/')}}/plugin/codestudies/edit/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}">{{$codestudy->id}}［無題］</li>

@endforeach
</ul>
