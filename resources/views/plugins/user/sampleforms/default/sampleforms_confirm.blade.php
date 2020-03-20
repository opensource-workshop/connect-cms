{{--
 * フォームのサンプル画面　確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

@if (empty($id))
<script type="text/javascript">
    {{-- Form のキャンセル用フォームのsubmit JavaScript --}}
    function submit_cancel( frame_id ) {
        sampleforms_form.action = "/plugin/sampleforms/create/{{$page->id}}/" + frame_id;
        sampleforms_form.submit();
    }
</script>
@else
<script type="text/javascript">
    {{-- Form のキャンセル用フォームのsubmit JavaScript --}}
    function submit_cancel( frame_id ) {
        sampleforms_form.action = "/plugin/sampleforms/edit/{{$page->id}}/" + frame_id + "/{{$id}}";
        sampleforms_form.submit();
    }
</script>
@endif

{{-- フォームの作成。フレームID を指定して、フレームを特定する --}}
@if (empty($id))
<form action="{{url('/')}}/redirect/plugin/sampleforms/save/{{$page->id}}/{{$frame_id}}" name="sampleforms_form" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
@else
<form action="{{url('/')}}/redirect/plugin/sampleforms/update/{{$page->id}}/{{$frame_id}}/{{$id}}" name="sampleforms_form" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
@endif

    {{csrf_field()}}
    <input name="id" type="hidden" value="{{$id}}">

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
        <td>
            <input name="column_text" type="hidden" value="{{old('column_text')}}">
            <div>{{old('column_text')}}</div>
        </td>
    </tr>
    <tr>
        <th>ファイル</th>
        <td>
            @if ($upload_files)
                <input name="upload_files[column_file][path]"                 type="hidden" value="{{$upload_files['column_file']['path']}}">
                <input name="upload_files[column_file][client_original_name]" type="hidden" value="{{$upload_files['column_file']['client_original_name']}}">
                <input name="upload_files[column_file][mimetype]"             type="hidden" value="{{$upload_files['column_file']['mimetype']}}">
                <div>{{$upload_files['column_file']['client_original_name']}}</div>
            @else
                @if ($sampleform)
                    <img src="{{url('/')}}/file/{{$sampleform->column_file}}" class="img-responsive">
                @endif
            @endif
        </td>
    </tr>
    <tr>
        <th>パスワード</th>
        <td>
            <input name="column_password" type="hidden" value="{{old('column_password')}}">
            <div>{{old('column_password')}}</div>
        </td>
    </tr>
    <tr>
        <th>テキストエリア</th>
        <td>
            <input name="column_textarea" type="hidden" value="{!!nl2br(e(old('column_textarea')))!!}">
            <div>{!!nl2br(e(old('column_textarea')))!!}</div>
        </td>
    </tr>
    <tr>
        <th>セレクト</th>
        <td>
            <input name="column_select" type="hidden" value="{{old('column_select')}}">
            <div>{{old('column_select')}}</div>
        </td>
    </tr>
    <tr>
        <th>チェックボックス</th>
        <td>
            @if (!old('column_checkbox')==null)
                @foreach (old('column_checkbox') as $column_checkbox_item)
                    <input name="column_checkbox[]" type="hidden" value="{{$column_checkbox_item}}">
                <div>{{$column_checkbox_item}}</div>
                @endforeach
            @endif
        </td>
    </tr>
    <tr>
        <th>ラジオボタン</th>
        <td>
            <input name="column_radio" type="hidden" value="{{old('column_radio')}}">
            <div>{{old('column_radio')}}</div>
        </td>
    </tr>
    </table>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary form-horizontal">
            @if (empty($id))
                登録確定
            @else
                変更確定
            @endif
        </button>
        <button type="button" onclick="javascript:submit_cancel({{$frame_id}});" class="btn btn-default" style="margin-left: 10px;">
            キャンセル
        </button>
    </div>

</form>

