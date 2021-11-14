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
            <label class="{{$frame->getSettingLabelClass()}}" for="folder_name">フォトアルバム名 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="folder_name[{{$frame_id}}]" value="{{old("folder_name.$frame_id")}}" class="form-control @if ($errors && $errors->has("folder_name.$frame_id")) border-danger @endif" id="folder_name{{$frame_id}}">
                @if ($errors && $errors->has("folder_name.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("folder_name.*")}}</div>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="description">説明</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea name="description[{{$frame_id}}]" class="form-control @if ($errors->has('description.$frame_id')) border-danger @endif" rows=2>{!!old("description.$frame_id")!!}</textarea>
                @if ($errors && $errors->has("description.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("description.*")}}</div>
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
        <div class="form-group row no-gutters">
            <label class="{{$frame->getSettingLabelClass()}} pr-3" for="upload_file">ファイル <label class="badge badge-danger">必須</label></label>
            <div class="custom-file {{$frame->getSettingInputClass()}}">
                <input type="hidden" name="upload_file[{{$frame_id}}]" value="">
                <input type="file" name="upload_file[{{$frame_id}}]" value="{{old("upload_file.$frame_id")}}" class="custom-file-input @if ($errors && $errors->has("upload_file.$frame_id")) border-danger @endif" id="upload_file{{$frame_id}}">
                <label class="custom-file-label ml-md-2" for="upload_file" data-browse="参照">ファイル選択...</label>
                @if ($errors && $errors->has("upload_file.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_file.*")}}</div>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="title">タイトル</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="title[{{$frame_id}}]" value="{{old("title.$frame_id")}}" class="form-control @if ($errors && $errors->has("title.$frame_id")) border-danger @endif" id="title{{$frame_id}}">
                <small class="form-text text-muted">空の場合、ファイル名をタイトルとして登録します。</small>
                @if ($errors && $errors->has("title.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("title.*")}}</div>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="description">説明</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea name="description[{{$frame_id}}]" class="form-control @if ($errors->has('description.$frame_id')) border-danger @endif" rows=2>{!!old("description.$frame_id")!!}</textarea>
                @if ($errors && $errors->has("description.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("description.*")}}</div>
                @endif
            </div>
        </div>
	    <div class="form-group row">
	        <label class="{{$frame->getSettingLabelClass()}} pt-0">アルバム表紙</label>
	        <div class="{{$frame->getSettingInputClass()}}">
	            <div class="custom-control custom-checkbox">
	                @if(old("is_cover.$frame_id"))
	                    <input type="checkbox" name="is_cover[{{$frame_id}}]" value="1" class="custom-control-input" id="is_cover{{$frame_id}}" checked=checked>
	                @else
	                    <input type="checkbox" name="is_cover[{{$frame_id}}]" value="1" class="custom-control-input" id="is_cover{{$frame_id}}">
	                @endif
	                <label class="custom-control-label" for="is_cover{{$frame_id}}">チェックすると、アルバムの表紙に使われます。</label>
	            </div>
	        </div>
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
                {{-- カバー画像が指定されていれば使用し、指定されていなければ、グレーのカバーを使用 --}}
                @if ($covers->where('parent_id', $photoalbum_content->id)->first())
                    <img src="/file/{{$covers->where('parent_id', $photoalbum_content->id)->first()->upload_id}}?size=small"
                         id="cover_{{$loop->iteration}}"
                         style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                         class="img-fluid"
                    >
                @else
                    <svg class="bd-placeholder-img card-img-top" width="100%" height="150" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Placeholder: Image cap">
                        <title>{{$photoalbum_content->name}}</title>
                        <rect fill="#868e96" width="100%" height="100%"></rect>
                        <text fill="#dee2e6"x="50%" y="50%" text-anchor="middle" dominant-baseline="central">{{$photoalbum_content->name}}</text>
                    </svg>
                @endif
            </a>
        </div>
    </div>
    <div class="col-sm-8 mt-3">
        <h5 class="card-title">{{$photoalbum_content->name}}</h5>
        <p class="card-text">{!!nl2br(e($photoalbum_content->description))!!}</p>
        <div class="d-flex justify-content-between align-items-center">

	        <div class="custom-control custom-checkbox">
	            <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->displayName}}">
	            <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
	        </div>
            @can('posts.update', [[$photoalbum_content, $frame->plugin_name, $buckets]])
            <a href="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" class="btn btn-sm btn-success">
                <i class="far fa-edit"></i> 編集
            </a>
            @endcan
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
            <img src="/file/{{$photoalbum_content->upload_id}}?size=small"
                 id="photo_{{$loop->iteration}}"
                 style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid" data-toggle="modal" data-target="#image_Modal_{{$loop->iteration}}"
            >
            <div class="modal fade" id="image_Modal_{{$loop->iteration}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel_{{$loop->iteration}}">
                <div class="modal-dialog modal-lg modal-middle">{{-- モーダルウィンドウの縦表示位置を調整・画像を大きく見せる --}}
                    <div class="modal-content pb-3" style="{{$photoalbum_content->getModalMinSize()}}">
                        <div class="modal-body mx-auto">
                            {{-- 拡大表示ウィンドウにも、初期設定でサムネイルを設定しておき、クリック時に実寸画像を読み込みなおす --}}
                            <img src="/file/{{$photoalbum_content->upload_id}}?size=small"
                                 style="object-fit: scale-down; cursor:pointer;"
                                 id="popup_photo_{{$loop->iteration}}"
                                 class="img-fluid"/>
                        </div>
                        <div class="modal-img_footer">
                            <h5 class="card-title">{{$photoalbum_content->name}}</h5>
                            <p class="card-text">{!!nl2br(e($photoalbum_content->description))!!}</p>
                            <button type="button" class="btn btn-success" data-dismiss="modal">閉じる</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">{{$photoalbum_content->name}}</h5>
                <p class="card-text">{!!nl2br(e($photoalbum_content->description))!!}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->name}}">
                        <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
                    </div>
                    @can('posts.update', [[$photoalbum_content, $frame->plugin_name, $buckets]])
                    <a href="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" class="btn btn-sm btn-success">
                        <i class="far fa-edit"></i> 編集
                    </a>
                    @endcan
                </div>
            </div>
            <script>
            {{-- サムネイル枠のクリックで、実寸画像を読み込む。一覧表示時のネットワーク通信量の軽減対応 --}}
            $("#photo_{{$loop->iteration}}").on("click", function() {
               $("#popup_photo_{{$loop->iteration}}").attr('src', "/file/{{$photoalbum_content->upload_id}}");
            });
            </script>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="bg-light mt-3 p-2 text-right">
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
