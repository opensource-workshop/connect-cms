{{--
 * コードスタディ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コードスタディプラグイン
 --}}


<form action="/plugin/codestudies/save/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

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
                    <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> 実行</button>
                </div>
            </div>
        </div>
    </div>
</form>

<ul>
@foreach($codestudies as $codestudy)

    <li><a href="{{URL::to('/')}}/plugin/codestudies/edit/{{$page->id}}/{{$frame_id}}/{{$codestudy->id}}">{{$codestudy->id}}［無題］</a></li>

@endforeach
</ul>

<div class="panel panel-default">
  <div class="panel-heading">実行結果</div>
  <div class="panel-body">
    {{$result}}
  </div>
</div>
