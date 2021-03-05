{{--
 * データ収集編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データ収集プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.receives.receives_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if (!$receive->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用するデータ収集設定を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($receive) || $create_flag)
                新しいデータ収集設定を登録します。
            @else
                データ収集設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (!$receive->id && !$create_flag)
@else
<form action="/plugin/receives/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにreceives_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="receives_id" value="">
    @else
        <input type="hidden" name="receives_id" value="{{$receive->id}}">
    @endif

    <div class="form-group">
        <label class="control-label">APIキー <label class="badge badge-danger">必須</label></label>
        <input type="text" name="key" value="{{old('key', $receive->key)}}" class="form-control">
        @if ($errors && $errors->has('key')) <div class="text-danger">{{$errors->first('key')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">APIトークン<label class="badge badge-danger">必須</label></label>
        <input type="text" name="token" value="{{old('token', $receive->token)}}" class="form-control">
        @if ($errors && $errors->has('token')) <div class="text-danger">{{$errors->first('token')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">データセット名<label class="badge badge-danger">必須</label></label>
        <input type="text" name="dataset_name" value="{{old('dataset_name', $receive->dataset_name)}}" class="form-control">
        @if ($errors && $errors->has('dataset_name')) <div class="text-danger">{{$errors->first('dataset_name')}}</div> @endif
    </div>

    {{-- テキストエリア --}}
    <div class="form-group">
        <label class="control-label">カラム</label>
        <textarea name="columns" rows="5" class="form-control">{{old('columns', $receive->columns)}}</textarea>
        @if ($errors && $errors->has('columns')) <div class="text-danger">{{$errors->first('columns')}}</div> @endif
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div>
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                <i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal mr-2"><i class="fas fa-check"></i>
                <span class="d-none d-xl-inline">
                @if (empty($error) || $create_flag)
                    登録
                @else
                    変更
                @endif
                </span>
            </button>

            {{-- 既存データ収集設定の場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
                <a data-toggle="collapse" href="#collapse{{$receive_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-xl-inline"> 削除</span></span>
                </a>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$receive_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データ収集設定を削除します。<br>このデータ収集設定で受信したデータも削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/receives/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$receive->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
