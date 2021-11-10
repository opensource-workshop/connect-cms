{{--
 * フォトアルバム画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
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
        });

        $('.custom-file-input').on('change',function(){
            $(this).next('.custom-file-label').html($(this)[0].files[0].name);
        });
        @endcan

        $('#app_{{$frame_id}} input[type="checkbox"][name="photoalbum_content_id[]"]').on('change', function(){

            $('#selected-contents{{$frame_id}}').html('');

            if ($('#app_{{$frame_id}} input[type="checkbox"][name="photoalbum_content_id[]"]:checked').length > 0){
                $('#app_{{$frame_id}} .btn-download').prop('disabled', false);
                $('#app_{{$frame_id}} input[type="checkbox"][name="photoalbum_content_id[]"]:checked').each(function(){
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
            $('#form-photoalbum-contents{{$frame_id}}').attr('action', '{{url('/')}}/download/plugin/photoalbums/download/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}');
            $('#form-photoalbum-contents{{$frame_id}}').submit();
        });

    });

    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    function deleteContents{{$frame_id}}() {
        if (window.confirm('データを削除します。\nよろしいですか？')) {
            $('#form-photoalbum-contents{{$frame_id}}').attr('action', '{{url('/')}}/redirect/plugin/photoalbums/deleteContents/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}');
            $('#form-photoalbum-contents{{$frame_id}}').attr('method', 'POST');
            $('#form-photoalbum-contents{{$frame_id}}').append('<input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">');

            $('#form-photoalbum-contents{{$frame_id}}').submit();
        }
    }
    @endcan
</script>
<div id="app_{{$frame_id}}">
@can('posts.create', [[null, $frame->plugin_name, $buckets]])
<div class="p-2 text-right mb-2">
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}"><i class="fas fa-folder-plus"></i><span class="d-none d-sm-inline"> アルバム作成</span></button>
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}" id="btn-upload-file"><i class="fas fa-file-upload"></i><span class="d-none d-sm-inline"> ファイル追加</span></button>
</div>
@endcan
<form action="{{url('/')}}/redirect/plugin/photoalbums/makeFolder/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("folder_name.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_mkdir{{$frame->id}}">
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="folder_name">フォトアルバム名</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="folder_name[{{$frame_id}}]" value="{{old("folder_name.$frame_id")}}" class="form-control @if ($errors && $errors->has("folder_name.$frame_id")) border-danger @endif" id="folder_name{{$frame_id}}">
                @if ($errors && $errors->has("folder_name.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("folder_name.*")}}</div>
                @endif
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit">作成</button>
        </div>
    </div>
</form>
<form action="{{url('/')}}/redirect/plugin/photoalbums/upload/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
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
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit">追加</button>
            <small id="upload-size-server-help" class="form-text text-muted">アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{UploadMaxSize::getDescription($photoalbum->upload_max_size)}}</span></small>
        </div>
    </div>
</form>
<ul class="breadcrumb bg-white">
@foreach($breadcrumbs as $breadcrumb)
    @if (!$loop->last)
        <li class="breadcrumb-item"><a href="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumb->id}}/#frame-{{$frame->id}}">{{$breadcrumb->name}}</a></li>
    @else
        <li class="breadcrumb-item active">{{$breadcrumb->name}}</li>
    @endif
@endforeach
</ul>
<form id="form-photoalbum-contents{{$frame_id}}" method="GET">
{{csrf_field()}}
<input type="hidden" name="parent_id" value="{{$parent_id}}">
@include('plugins.common.errors_inline', ['name' => 'photoalbum_content_id'])
<div class="bg-light p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame->id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>

<style>
.modal-middle {        //モーダルウィンドウの縦表示位置を調整
    margin: 5% auto;
}

.modal-img_footer {    //表示予定のテキストとボタンを中央揃え
    padding: .5em;
    text-align: center;
}
</style>

{{-- ルート要素の表示時は「1つ上へ」を表示しない --}}
@if (count($breadcrumbs) > 1)
    <ul class="breadcrumb bg-white">
        <li class="breadcrumb-item active"><i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumbs->last()->parent_id}}/#frame-{{$frame->id}}">1つ上へ</a></li>
    </ul>
@endif

{{-- データ一覧にアルバムが含まれる場合 --}}
@if ($photoalbum_contents->where('is_folder', 1)->isNotEmpty())
<div class="row">
    @foreach($photoalbum_contents->where('is_folder', 1) as $photoalbum_content)
    <div class="col-sm-4 mt-3">
        <div class="card sm-4">
            <a href="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}/#frame-{{$frame->id}}" class="text-center">
                <img src="/debug/images/DSC_1941-3.JPG"
                     style="max-height: 150px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                     class="img-fluid"
                >
            </a>
        </div>
    </div>
    <div class="col-sm-8 mt-3">
        <h5 class="card-title">{{$photoalbum_content->name}}</h5>
        <p class="card-text">This is a wider card with supporting text below as a natural</p>

        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->displayName}}">
            <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- データ一覧に画像が含まれる場合 --}}
@if ($photoalbum_contents->where('is_folder', 0)->isNotEmpty())
<div class="row">
    @foreach($photoalbum_contents->where('is_folder', 0) as $photoalbum_content)
    <div class="col-md-4">
        <div class="card mt-3 shadow-sm">
            <img src="/file/{{$photoalbum_content->upload_id}}"
                 id="photo_{{$loop->iteration}}"
                 style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid" data-toggle="modal" data-target="#image_Modal_{{$loop->iteration}}"
            >
            <div class="modal fade" id="image_Modal_{{$loop->iteration}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel_{{$loop->iteration}}">
                <div class="modal-dialog modal-lg modal-middle">{{-- モーダルウィンドウの縦表示位置を調整・画像を大きく見せる --}}
                    <div class="modal-content pb-3">
                        <div class="modal-body mx-auto">
                            <img src="/file/{{$photoalbum_content->upload_id}}"
                                 style="max-height: 800px; object-fit: scale-down; cursor:pointer;"
                                 class="img-fluid" />
                        </div>
                        <div class="modal-img_footer">
                            <h5 class="card-title">{{$photoalbum_content->name}}</h5>
                            <p class="card-text">This is a wider card with supporting text below as a natural</p>
                            <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">{{$photoalbum_content->name}}</h5>
                <p class="card-text">This is a wider card with supporting text below.</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->name}}">
                        <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="pop">Edit</button>
                    </div>
                </div>
            </div>
            <script>
            $("#pop").on("click", function() {
               $("#photo_{{$loop->iteration}}").modal();
            });
            </script>
        </div>
    </div>
    @endforeach
</div>
@endif

<hr />

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
{{--
        @if (count($breadcrumbs) > 1)
            <tr>
                <td colspan="4"><i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumbs->last()->parent_id}}/#frame-{{$frame->id}}">1つ上へ</a></td>
            </tr>
        @endif

        @if ($photoalbum_contents->count() === 0)
            <tr>
                <td colspan="4">ファイルがありません</td>
            </tr>
        @else
            @foreach($photoalbum_contents as $photoalbum_content)
                <tr>
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="customCheck{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->displayName}}">
                            <label class="custom-control-label" for="customCheck{{$photoalbum_content->id}}"></label>
                        </div>
                    </td>
                    @if ($photoalbum_content->is_folder == true)
                        <td>
                            <i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}/#frame-{{$frame->id}}">{{$photoalbum_content->displayName}}</a>
                            <small class="form-text text-muted d-block d-md-none">
                                - | {{$photoalbum_content->created_at}}
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">-</td>
                        <td class="d-none d-md-table-cell">{{$photoalbum_content->created_at}}</td>
                    @else
                        <td>
                            <i class="far fa-file mr-1 text-secondary"></i><a href="{{url('/')}}/file/{{$photoalbum_content->upload_id}}" target="_blank">{{$photoalbum_content->displayName}}</a>
                            <small class="form-text text-muted d-block d-md-none">
                                {{$photoalbum_content->upload->getFormatSize()}} | {{$photoalbum_content->created_at}}
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">{{$photoalbum_content->upload->getFormatSize()}}</td>
                        <td class="d-none d-md-table-cell">{{$photoalbum_content->updated_at}}</td>
                    @endif
                </tr>
            @endforeach
        @endif
--}}
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
