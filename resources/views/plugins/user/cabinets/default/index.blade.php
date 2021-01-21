{{--
 * キャビネット画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<span class="badge badge-pill badge-primary mb-1">フォルダ一覧</span>
<ul class="list-group">
{{--    <li class="list-group-item"><i class="fas fa-arrow-circle-up"></i></li> --}}
    <li class="list-group-item"><i class="far fa-folder"></i> Item #1</li>
    <li class="list-group-item"><i class="far fa-chevron-right"></i> <i class="far fa-folder"></i> Item #1-1</li>
    <li class="list-group-item">　<i class="far fa-chevron-right"></i> <i class="far fa-folder-open"></i> Item #1-1-1</li>
    <li class="list-group-item"><i class="far fa-folder"></i> Item #3</li>
    <li class="list-group-item"><i class="far fa-folder"></i> Item #3</li>
</ul>

<span class="badge badge-pill badge-primary mt-3 mb-1">参照フォルダ</span>
<div class="clearfix d-xl-flex">
    <div class="alert alert-secondary mb-1 mr-1 p-2">
        Item #1 > Item #1 / Item #1-1 / Item #1-1-1
    </div>
    <div class="text-nowrap">
        <button type="button" class="btn btn-success p-2" data-toggle="collapse" data-target="#fileUpload" aria-expanded="false" aria-controls="fileUpload" onclick="$('#folderCreate').collapse('hide')">
            <i class="fas fa-file-medical"></i> ファイル追加
        </button>
        <button type="button" class="btn btn-success p-2" data-toggle="collapse" data-target="#folderCreate" aria-expanded="false" aria-controls="folderCreate" onclick="$('#fileUpload').collapse('hide')">
            <i class="fas fa-folder-plus"></i> フォルダ作成
        </button>
    </div>
</div>

<div class="card collapse mb-3 border-primary" id="fileUpload">
    <div class="card-body">
        <div class="form-group row">
            <label class="col-md-3 control-label text-md-right"><label class="badge badge-danger">必須</label> ファイル</label>
            <div class="col-md-9">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="post_file" name="post_file">
                    <label class="custom-file-label" for="post_file" data-browse="参照">ファイル</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 control-label text-md-right">説明</label>
            <div class="col-md-9">
                <input type="text" name="description" value="" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href=''">
                            <i class="fas fa-times"></i><span class="d-none d-lg-inline"> キャンセル</span>
                        </button>
                        <button type="button" class="btn btn-info mr-2"><i class="far fa-save"></i><span class="d-none d-sm-inline"> 一時保存</span></button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-upload"></i> アップロード</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card collapse mb-3 border-primary" id="folderCreate">
    <div class="card-body">
        <div class="form-group row">
            <label class="col-md-3 control-label text-md-right"><label class="badge badge-danger">必須</label> フォルダ名</label>
            <div class="col-md-9">
                <input type="text" name="folder_name" value="" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 control-label text-md-right">説明</label>
            <div class="col-md-9">
                <input type="text" name="description" value="" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-12">
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href=''">
                            <i class="fas fa-times"></i><span class="d-none d-lg-inline"> キャンセル</span>
                        </button>
                        <button type="button" class="btn btn-info mr-2"><i class="far fa-save"></i><span class="d-none d-sm-inline"> 一時保存</span></button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> フォルダ作成</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<span class="badge badge-pill badge-primary mt-3 mb-1">ファイル</span>
<ul class="list-group">
    <li class="list-group-item"><i class="far fa-file"></i> Item #1</li>
    <li class="list-group-item"><i class="far fa-file-word"></i> Item #3</li>
    <li class="list-group-item"><i class="far fa-file-powerpoint"></i> Item #3</li>
</ul>

@endsection
