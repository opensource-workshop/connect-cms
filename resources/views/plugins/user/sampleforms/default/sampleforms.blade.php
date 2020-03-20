{{--
 * フォームのサンプル画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

@foreach($sampleforms as $sampleform)
    <table class="table table-bordered cc_responsive_table">
    <thead>
    <tr class="active">
        <th class="col-xs-3">項目</th>
        <th class="col-xs-9">登録内容</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th>テキスト</th>
        <td>{{$sampleform->column_text}}</td>
    </tr>
    <tr>
        <th>ファイル</th>
        <td><img src="{{url('/')}}/file/{{$sampleform->column_file}}" class="img-responsive"></td>
    </tr>
    <tr>
        <th>パスワード</th>
        <td>{{$sampleform->column_password}}</td>
    </tr>
    <tr>
        <th>テキストエリア</th>
        <td>{!!$sampleform->column_textarea!!}</td>
    </tr>
    <tr>
        <th>セレクト</th>
        <td>{{$sampleform->column_select}}</td>
    </tr>
    <tr>
        <th>チェックボックス</th>
        <td>{{str_replace('|', ', ', trim($sampleform->column_checkbox, "|"))}}</td>
    </tr>
    <tr>
        <th>ラジオボタン</th>
        <td>{{$sampleform->column_radio}}</td>
    </tr>
    </tbody>
    </table>
    @auth
        <p class="text-right" style="margin-top: -15px;">
            <a href="{{url('/')}}/plugin/sampleforms/edit/{{$page->id}}/{{$frame_id}}/{{$sampleform->id}}">
                <span class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-edit"></span> <span class="hidden-xs">編集</span></span>
            </a>
            <a data-toggle="collapse" href="#collapse{{$sampleform->id}}">
                <span class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span> <span class="hidden-xs">削除</span></span>
            </a>
        </p>

        <div id="collapse{{$sampleform->id}}" class="collapse">
            <div class="panel panel-danger">
                <div class="panel-body">
                    <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                    <div class="text-center">
                        {{-- 削除ボタン --}}
                        <form action="{{url('/')}}/redirect/plugin/sampleforms/destroy/{{$page->id}}/{{$frame_id}}/{{$sampleform->id}}" method="POST">
                            {{csrf_field()}}
                            <input type="hidden" name="page" value="{{Request::get('page')}}">
                            <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"span class="glyphicon glyphicon-trash"></span> 本当に削除する</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    @endauth
@endforeach

{{-- ページング処理 --}}
<div class="text-center">
        {{ $sampleforms->links() }}
</div>

@auth
    <p class="text-center">
        {{-- 新規登録ボタン --}}
        <button type="button" class="btn btn-primary" onclick="location.href='{{url('/')}}/plugin/sampleforms/create/{{$page->id}}/{{$frame_id}}'">新規登録</button>
    </p>
@endauth

