{{--
 * CSVインポート画面テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ダウンロード用フォーム --}}
<form action="{{url('/')}}/download/plugin/databases/downloadCsvFormat/{{$page->id}}/{{$frame_id}}/{{$database->id}}" method="post" name="database_download_csv_format">
    {{ csrf_field() }}
</form>

@if (session('flash_message'))
    <div class="alert alert-success">
        {{ session('flash_message') }}
    </div>
@endif

<div class="alert alert-info" role="alert">
    <ul class="pl-3">
        <li>CSVファイルを使って、データベースへ一括登録できます。詳細は<a href="https://connect-cms.jp/manual/user/database#frame-178" target="_blank">こちら</a>を参照してください。</li>
    </ul>
</div>

{{-- post先 --}}
<form action="{{url('/')}}/redirect/plugin/databases/uploadCsv/{{$page->id}}/{{$frame_id}}/{{$database->id}}#frame-{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    {{ csrf_field() }}
    {{-- post後、再表示するURL --}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/databases/import/{{$page->id}}/{{$frame_id}}/{{$database->id}}#frame-{{$frame_id}}">

    <div class="form-group row">
        <div class="col text-right">
            <a href="#frame-{{$frame_id}}" onclick="database_download_csv_format.submit();">
                CSVファイルのフォーマット
            </a>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">データベース名</label>
        <div class="{{$frame->getSettingInputClass()}}">
            {{$database->databases_name}}
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">CSVファイル <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="file" name="databases_csv" class="form-control-file">
            @if ($errors && $errors->has('databases_csv'))
                @foreach ($errors->get('databases_csv') as $message)
                    <div class="text-danger">{{$message}}</div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}" onclick="return confirm('インポートします。\nよろしいですか？')">
                        インポート
                    </span>
                </button>
            </div>

        </div>
    </div>
</form>

@endsection
