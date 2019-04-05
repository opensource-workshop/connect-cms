{{--
 * フォームのサンプル画面　確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

<script type="text/javascript">
    {{-- Form のキャンセル用フォームのsubmit JavaScript --}}
    function submit_cancel( id ) {
        sampleforms_form.action = "/?action=create&frame_id=" + id;
        sampleforms_form.submit();
    }
</script>

{{-- フォームの作成。フレームID を指定して、フレームを特定する --}}
@if (empty($id))
<form action="{{URL::to($page->permanent_link)}}?action=save&frame_id={{$frame_id}}" name="sampleforms_form" method="POST">
@else
<form action="{{URL::to($page->permanent_link)}}?action=update&frame_id={{$frame_id}}&id={{$id}}" name="sampleforms_form" method="POST">
@endif
    {{ csrf_field() }}

    {{-- テキスト --}}
    <div class="form-group">
        <label for="name">テキスト</label>
        <input name="column_text" type="hidden" value="{{old('column_text')}}">
        <div>{{old('column_text')}}</div>
    </div>

{{-- ファイル --}}
<div class="form-group">
    <label for="name">ファイル</label>
    <input name="column_file" type="hidden" value="{{$tmp_filename}}">
    <div>{{$upload_filename}}</div>
</div>

{{-- パスワード --}}
<div class="form-group">
    <label for="name">パスワード</label>
    <input name="column_password" type="hidden" value="{{old('column_password')}}">
    <div>{{old('column_password')}}</div>
</div>

{{-- テキストエリア --}}
<div class="form-group">
    <label for="name">テキストエリア</label>
    <input name="column_textarea" type="hidden" value="{!!nl2br(e(old('column_textarea')))!!}">
    <div>{!!nl2br(e(old('column_textarea')))!!}</div>
</div>

{{-- セレクト --}}
<div class="form-group">
    <label for="name">セレクト</label>
    <input name="column_select" type="hidden" value="{{old('column_select')}}">
    <div>{{old('column_select')}}</div>
</div>

{{-- チェックボックス --}}
<div class="form-group">
    <label for="name">チェックボックス</label>
    @if (!old('column_checkbox')==null)
        @foreach (old('column_checkbox') as $column_checkbox_item)
            <input name="column_checkbox[]" type="hidden" value="{{$column_checkbox_item}}">
            <div>{{$column_checkbox_item}}</div>
        @endforeach
    @endif
</div>

{{-- ラジオボタン --}}
<div class="form-group">
    <label for="name">ラジオボタン</label>
    <input name="column_radio" type="hidden" value="{{old('column_radio')}}">
    <div>{{old('column_radio')}}</div>
</div>

{{-- Submitボタン --}}
<div class="form-group text-center">
    <button type="submit" class="btn btn-primary form-horizontal">
        フォーム保存
    </button>
    <button type="button" onclick="javascript:submit_cancel({{$frame_id}});" class="btn btn-default" style="margin-left: 10px;">
        キャンセル
    </button>
</div>

</form>

