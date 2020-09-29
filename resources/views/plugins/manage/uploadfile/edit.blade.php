{{--
 * アップロードファイル管理の編集テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.uploadfile.uploadfile_manage_tab')
    </div>
    <div class="card-body">
        <form action="{{url('/manage/uploadfile/save')}}/{{$upload->id}}" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            @if (session('info_message'))
                <div class="alert alert-info">
                    {{session('info_message')}}
                </div>
            @endif

            {{-- ID --}}
            <div class="form-group row">
                <label for="upload_id" class="col-md-4 col-form-label text-md-right">ID</label>
                <div class="col-md-8 col-form-label">{{$upload->id}}</div>
            </div>

            {{-- ファイル名 --}}
            <div class="form-group row">
                <label for="client_original_name" class="col-md-4 col-form-label text-md-right">ファイル名</label>
                <div class="col-md-8">
                    <input type="text" name="client_original_name" id="client_original_name" value="{{old('client_original_name', $upload->getFilename())}}" class="form-control">
                    @if ($errors && $errors->has('client_original_name')) <div class="text-danger">{{$errors->first('client_original_name')}}</div> @endif
                </div>
            </div>

            {{-- ダウンロード --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">ダウンロード</label>
                <div class="col-md-8 col-form-label"><a href="{{url('/')}}/file/{{$upload->id}}" target="_blank">{{$upload->client_original_name}}</a></div>
            </div>

            {{-- 拡張子 --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">拡張子</label>
                <div class="col-md-8 col-form-label">{{$upload->extension}}</div>
            </div>

            {{-- サイズ --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">サイズ</label>
                <div class="col-md-8 col-form-label">{{$upload->getFormatSize()}}</div>
            </div>

            {{-- アップロード日時 --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">アップロード日時</label>
                <div class="col-md-8 col-form-label">{{$upload->created_at}}</div>
            </div>

            {{-- ダウンロード数 --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">ダウンロード数</label>
                <div class="col-md-8 col-form-label">{{$upload->download_count}}</div>
            </div>

            {{-- アップロード・ページ --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">アップロード・ページ</label>
                <div class="col-md-8 col-form-label">{!!$upload->getPageLinkTag('_blank')!!}</div>
            </div>

            {{-- アップロード・プラグイン --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">アップロード・プラグイン</label>
                <div class="col-md-8 col-form-label">{{$upload->getPluginNameFull()}}</div>
            </div>

            {{-- mimetype --}}
            <div class="form-group row">
                <label for="mimetype" class="col-md-4 col-form-label text-md-right">mimetype</label>
                <div class="col-md-8 col-form-label">{{$upload->mimetype}}</div>
            </div>

            {{-- 一時保存フラグ --}}
            <div class="form-group row">
                <label for="extension" class="col-md-4 col-form-label text-md-right">一時保存フラグ</label>
                <div class="col-md-8 col-form-label">{{$upload->getTemporaryFlagStr()}}</div>
            </div>

            {{-- ボタンエリア --}}
            <div class="form-group text-center">
                <div class="row">
                    <div class="mx-auto">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/uploadfile')}}'">
                            <i class="fas fa-times"></i> キャンセル
                        </button>
                        <button type="submit" class="btn btn-primary form-horizontal">
                            <i class="fas fa-check"></i> 更新
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
