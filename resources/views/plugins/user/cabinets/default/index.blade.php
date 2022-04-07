{{--
 * キャビネット画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<script type="text/javascript">
    $(function () {
        @can('posts.create', [[null, $frame->plugin_name, $buckets]])
        $('#collapse_mkdir{{$frame->id}}').on('hidden.bs.collapse', function () {
            $('#folder_name{{$frame_id}}').val('');
        });

        $('#collapse_mkdir{{$frame->id}}').on('show.bs.collapse', function () {
            $('#collapse_upload{{$frame->id}}').collapse('hide');
        });

        $('#collapse_upload{{$frame->id}}').on('show.bs.collapse', function () {
            $('#collapse_mkdir{{$frame->id}}').collapse('hide');
        });

        $('#collapse_upload{{$frame->id}}').on('hidden.bs.collapse', function () {
            $('#upload_file{{$frame_id}}').val('');
            $('#upload_file{{$frame_id}}').next('.custom-file-label').html('ファイル選択...');
            $('#checkbox_zip_deploy').addClass('d-none');
            $('#zip_deploy').prop('checked', false);
        });

        $('.custom-file-input').on('change',function(){
            let filename = $(this)[0].files[0].name;
            let extension = filename.split('.').pop();
            $(this).next('.custom-file-label').html(filename);
            // ZIPファイルが選択されたら、ZIP展開のオプションを表示する
            if (extension === 'zip') {
                $('#checkbox_zip_deploy').removeClass('d-none');
            } else {
                $('#checkbox_zip_deploy').addClass('d-none');
                $('#zip_deploy').prop('checked', false);
            }
        });


        @endcan

        $('#app_{{$frame_id}} input[type="checkbox"][name="cabinet_content_id[]"]').on('change', function(){

            $('#selected-contents{{$frame_id}}').html('');

            if ($('#app_{{$frame_id}} input[type="checkbox"][name="cabinet_content_id[]"]:checked').length > 0){
                $('#app_{{$frame_id}} .btn-download').prop('disabled', false);
                $('#app_{{$frame_id}} input[type="checkbox"][name="cabinet_content_id[]"]:checked').each(function(){
                    $('#selected-contents{{$frame_id}}').append('<li>' + $(this).data('name') + '</li>');
                })
                @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
                $('#app_{{$frame_id}} .btn-delete').prop('disabled', false);
                @endcan
            } else {
                $('#app_{{$frame_id}} .btn-download').prop('disabled', true);
                @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
                $('#app_{{$frame_id}} .btn-delete').prop('disabled', true);
                @endcan
            }
        });

        $('#app_{{$frame_id}} .btn-download').on('click', function(){
            $('#form-cabinet-contents{{$frame_id}}').attr('action', '{{url('/')}}/download/plugin/cabinets/download/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}');
            $('#form-cabinet-contents{{$frame_id}}').submit();
        });

    });

    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    function deleteContents{{$frame_id}}() {
        if (window.confirm('データを削除します。\nよろしいですか？')) {
            $('#form-cabinet-contents{{$frame_id}}').attr('action', '{{url('/')}}/redirect/plugin/cabinets/deleteContents/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}');
            $('#form-cabinet-contents{{$frame_id}}').attr('method', 'POST');
            $('#form-cabinet-contents{{$frame_id}}').append('<input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">');

            $('#form-cabinet-contents{{$frame_id}}').submit();
        }
    }
    @endcan
</script>
<div id="app_{{$frame_id}}">
@can('posts.create', [[null, $frame->plugin_name, $buckets]])
<div class="p-2 text-right mb-2">
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}"><i class="fas fa-folder-plus"></i><span class="d-none d-sm-inline"> フォルダ作成</span></button>
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}" id="btn-upload-file"><i class="fas fa-file-upload"></i><span class="d-none d-sm-inline"> ファイル追加</span></button>
</div>
@endcan
<form action="{{url('/')}}/redirect/plugin/cabinets/makeFolder/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("folder_name.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_mkdir{{$frame->id}}">
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="folder_name">フォルダ名</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="folder_name[{{$frame_id}}]" value="{{old("folder_name.$frame_id")}}" class="form-control @if ($errors && $errors->has("folder_name.$frame_id")) border-danger @endif" id="folder_name{{$frame_id}}">
                @if ($errors && $errors->has("folder_name.$frame_id"))
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("folder_name.*")}}</div>
                @endif
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit" id="button_make_folder{{$frame->id}}">作成</button>
        </div>
    </div>
</form>
<form action="{{url('/')}}/redirect/plugin/cabinets/upload/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("upload_file.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_upload{{$frame->id}}">
        <div class="form-group">
            <div class="custom-file">
                <input type="hidden" name="upload_file[{{$frame_id}}]" value="">
                <input type="file" name="upload_file[{{$frame_id}}]" value="{{old("upload_file.$frame_id")}}" class="custom-file-input @if ($errors && $errors->has("upload_file.$frame_id")) border-danger @endif" id="upload_file{{$frame_id}}">
                <label class="custom-file-label" for="upload_file" data-browse="参照">ファイル選択...</label>
            </div>
            @if ($errors && $errors->has("upload_file.$frame_id"))
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_file.*")}}</div>
            @endif
        </div>
        <div class="form-group d-none" id="checkbox_zip_deploy">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="zip_deploy" name="zip_deploy" value="true">
                <label class="custom-control-label" for="zip_deploy">ZIPを展開する</label>
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit" id="button_upload_file{{$frame->id}}">追加</button>
            <small id="upload-size-server-help" class="form-text text-muted">アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{UploadMaxSize::getDescription($cabinet->upload_max_size)}}</span></small>
        </div>
    </div>
</form>
<ul class="breadcrumb bg-white">
@foreach($breadcrumbs as $breadcrumb)
    @if (!$loop->last)
        <li class="breadcrumb-item"><a href="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumb->id}}/#frame-{{$frame->id}}">{{$breadcrumb->name}}</a></li>
    @else
        <li class="breadcrumb-item active">{{$breadcrumb->name}}</li>
    @endif
@endforeach
</ul>
<form id="form-cabinet-contents{{$frame_id}}" method="GET">
{{csrf_field()}}
<input type="hidden" name="parent_id" value="{{$parent_id}}">
@include('plugins.common.errors_inline', ['name' => 'cabinet_content_id'])
<div class="bg-light p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame->id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>
<table class="table text-break">
    <thead>
        <tr class="d-none d-md-table-row">
            <th>&nbsp;</th>
            <th>名前</th>
            <th>サイズ</th>
            <th>更新日</th>
        </tr>
    </thead>
    <tbody>
        {{-- ルート要素の表示時は「1つ上へ」を表示しない --}}
        @if (count($breadcrumbs) > 1)
            <tr>
                <td colspan="4"><i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumbs->last()->parent_id}}/#frame-{{$frame->id}}">1つ上へ</a></td>
            </tr>
        @endif

        @if ($cabinet_contents->count() === 0)
            <tr>
                <td colspan="4">ファイルがありません</td>
            </tr>
        @else
            @foreach($cabinet_contents as $cabinet_content)
                <tr>
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="customCheck{{$cabinet_content->id}}" name="cabinet_content_id[]" value="{{$cabinet_content->id}}" data-name="{{$cabinet_content->displayName}}">
                            <label class="custom-control-label" for="customCheck{{$cabinet_content->id}}"></label>
                        </div>
                    </td>
                    @if ($cabinet_content->is_folder == true)
                        <td>
                            <i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$cabinet_content->id}}/#frame-{{$frame->id}}">{{$cabinet_content->displayName}}</a>
                            <small class="form-text text-muted d-block d-md-none">
                                - | {{$cabinet_content->created_at}}
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">-</td>
                        <td class="d-none d-md-table-cell">{{$cabinet_content->created_at}}</td>
                    @else
                        <td>
                            <i class="far fa-file mr-1 text-secondary"></i><a href="{{url('/')}}/file/{{$cabinet_content->upload_id}}" target="_blank">{{$cabinet_content->displayName}}</a>
                            <small class="form-text text-muted d-block d-md-none">
                                {{$cabinet_content->upload->getFormatSize()}} | {{$cabinet_content->created_at}}
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">{{$cabinet_content->upload->getFormatSize()}}</td>
                        <td class="d-none d-md-table-cell">{{$cabinet_content->updated_at}}</td>
                    @endif
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
<div class="bg-light p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame_id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>
</form>
@can('posts.delete', [[null, $frame->plugin_name, $buckets]])
{{-- 削除確認モーダルウィンドウ --}}
<div class="modal" id="delete-confirm{{$frame_id}}" tabindex="-1" role="dialog" aria-labelledby="delete-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- ヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title" id="delete-title">削除確認</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- メインコンテンツ --}}
            <div class="modal-body">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="text-danger">以下のデータを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</div>
                        <ul class="text-danger" id="selected-contents{{$frame_id}}"></ul>
                    </div>
                    <div class="text-center mb-2">
                        {{-- キャンセルボタン --}}
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> キャンセル
                        </button>
                        {{-- 削除ボタン --}}
                        <button type="button" class="btn btn-danger" onclick="deleteContents{{$frame_id}}()"><i class="fas fa-check"></i> 本当に削除する</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
</div>
@endsection
