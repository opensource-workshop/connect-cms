{{--
 * フォームのサンプル画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

@foreach($sampleforms as $sampleform)
    <table class="table table-bordered">
    <thead>
    <tr class="active">
        <th class="col-xs-4">項目</th>
        <th class="col-xs-8">登録内容</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th>テキスト</th>
        <td>{{$sampleform->column_text}}</td>
    </tr>
    <tr>
        <th>ファイル</th>
        <td>{{$sampleform->column_file}}</td>
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
            <a href="{{URL::to($page->permanent_link)}}/?action=edit&frame_id={{$frame_id}}&id={{$sampleform->id}}">
                <span class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-edit"></span> <span class="hidden-xs">編集</span></span>
            </a>
        </p>
    @endauth
@endforeach
@auth
    <p class="text-center">
        {{-- 新規登録ボタン --}}
        <button type="button" class="btn btn-primary" onclick="location.href='{{URL::to($page->permanent_link)}}/?action=create&frame_id={{$frame_id}}'">新規登録</button>
    </p>
@endauth

