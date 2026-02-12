{{--
 * フォトアルバム画面テンプレート（ヘッダ）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
<script type="text/javascript">
    $(function () {
        {{-- ボタンでのフォームの折り畳み操作 --}}
        @can('posts.create', [[null, $frame->plugin_name, $buckets]])

        {{-- アルバム操作 --}}
        $('#collapse_mkdir{{$frame->id}}').on('hidden.bs.collapse', function () {
            $('#folder_name{{$frame_id}}').val('');
        });

        $('#collapse_mkdir{{$frame->id}}').on('show.bs.collapse', function () {
            $('#collapse_upload{{$frame->id}}').collapse('hide');
            $('#collapse_video{{$frame->id}}').collapse('hide');
        });

        {{-- 画像ファイル操作 --}}
        $('#collapse_upload{{$frame->id}}').on('show.bs.collapse', function () {
            $('#collapse_mkdir{{$frame->id}}').collapse('hide');
            $('#collapse_video{{$frame->id}}').collapse('hide');
        });

        $('#collapse_upload{{$frame->id}}').on('hidden.bs.collapse', function () {
            $('#upload_file{{$frame_id}}').val('');
            $('#upload_file{{$frame_id}}').next('.custom-file-label').html('画像ファイル選択...');
        });

        {{-- 動画ファイル操作 --}}
        $('#collapse_video{{$frame->id}}').on('show.bs.collapse', function () {
            $('#collapse_mkdir{{$frame->id}}').collapse('hide');
            $('#collapse_upload{{$frame->id}}').collapse('hide');
        });

        $('#collapse_video{{$frame->id}}').on('hidden.bs.collapse', function () {
            $('#upload_video{{$frame_id}}').val('');
            $('#upload_video{{$frame_id}}').next('.custom-file-label').html('動画ファイル選択...');
        });

        {{-- カスタムインプット対応（change でファイル名を明示的に設定する必要あり） --}}
        $('#upload_file{{$frame_id}}').on('change',function(){
            $(this).next('.custom-file-label').html($(this)[0].files[0].name);
        });
        $('#upload_video{{$frame_id}}').on('change',function(){
            $(this).next('.custom-file-label').html($(this)[0].files[0].name);
        });
        $('#upload_poster{{$frame_id}}').on('change',function(){
            $(this).next('.custom-file-label').html($(this)[0].files[0].name);
        });

        {{-- ポスター画像が選択されたら、アルバム表紙のチェックが可能にする --}}
        $('#upload_poster{{$frame_id}}').change(function(){
            $('#poster_is_cover{{$frame_id}}').prop('disabled', false);
        });
        @endcan

        {{-- 一覧のチェックボックスによる削除、ダウンロードの制御(編集権限あり or フレームでダウンロードOK) --}}
        @if ($download_check)
        $('#app_{{$frame_id}}').on('change', 'input[type="checkbox"][name="photoalbum_content_id[]"]', function(){

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

        {{-- 一覧のチェックボックスによるダウンロードの処理 --}}
        $('#app_{{$frame_id}} .btn-download').on('click', function(){
            $('#form-photoalbum-contents{{$frame_id}}').attr('action', '{{url('/')}}/download/plugin/photoalbums/download/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}');
            $('#form-photoalbum-contents{{$frame_id}}').submit();
        });
        @endif

        // 埋め込みコードの表示（もっと見るで追加された要素にも対応するため委譲）
        $('#app_{{$frame_id}}').on('click', '.embed_code_check', function (event) {
            event.preventDefault();

            var targetId = $(this).data('name');
            if (!targetId) {
                return;
            }

            $("#" + targetId).slideToggle();
            $("#" + targetId).focus();
            $("#" + targetId).select();
        });
    });

    {{-- 一覧のチェックボックスによる削除の処理 --}}
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

{{-- 作成系ボタン --}}
@can('posts.create', [[null, $frame->plugin_name, $buckets]])
<div class="p-2 text-right mb-2">
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}"><i class="fas fa-folder-plus"></i><span class="d-none d-sm-inline"> アルバム作成</span></button>
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}" id="btn-upload-file"><i class="fas fa-file-upload"></i><span class="d-none d-sm-inline"> 画像ファイル追加</span></button>
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_video{{$frame->id}}" id="btn-upload-video"><i class="fas fa-file-video"></i><span class="d-none d-sm-inline"> 動画ファイル追加</span></button>
</div>
@endcan

{{-- アルバム作成フォーム --}}
<form action="{{url('/')}}/redirect/plugin/photoalbums/makeFolder/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("folder_name.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_mkdir{{$frame->id}}">
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="folder_name">アルバム名 <label class="badge badge-danger">必須</label></label>
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
            <button class="btn btn-primary btn-sm" type="submit" id="button_make_folder{{$frame->id}}">作成</button>
        </div>
    </div>
</form>

{{-- 画像ファイルアップロードフォーム --}}
<form action="{{url('/')}}/redirect/plugin/photoalbums/upload/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("upload_file.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_upload{{$frame->id}}">
        <div class="form-group row no-gutters mb-0">
            <label class="{{$frame->getSettingLabelClass()}} pr-3 pb-0" for="upload_file">画像ファイル <label class="badge badge-danger">必須</label></label>
            <div class="custom-file {{$frame->getSettingInputClass()}}">
                <input type="hidden" name="upload_file[{{$frame_id}}]" value="">
                <input type="file" name="upload_file[{{$frame_id}}]" value="{{old("upload_file.$frame_id")}}" class="custom-file-input" id="upload_file{{$frame_id}}">
                <label class="custom-file-label ml-md-2 @if ($errors && $errors->has("upload_file.$frame_id")) border-danger @endif" for="upload_file" data-browse="参照">画像ファイル選択...</label>
            </div>
        </div>
        {{-- カスタムインプットで、ファイル行のマージン等が制御できないので、項目注釈やエラーを別の行で作成 --}}
        <div class="row">
            <label class="{{$frame->getSettingLabelClass()}} p-0"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <small class="my-0 form-text text-muted">jpg, png, gif, zip を許可します。<br />zip の場合は展開され、フォルダがアルバム（サブアルバム）となり、登録されます。</small>
            </div>
        </div>
        @if ($errors && $errors->has("upload_file.$frame_id"))
        <div class="form-group row mb-0">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_file.*")}}</div>
            </div>
        </div>
        @endif
        <div class="form-group row mt-md-3">
            <label class="{{$frame->getSettingLabelClass()}}" for="title">タイトル</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="title[{{$frame_id}}]" value="{{old("title.$frame_id")}}" class="form-control @if ($errors && $errors->has("title.$frame_id")) border-danger @endif" id="title{{$frame_id}}">
                <small class="form-text text-muted">空の場合、ファイル名をタイトルとして登録します。(zipの場合はファイル名が入ります)</small>
                @if ($errors && $errors->has("title.$frame_id"))
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("title.*")}}</div>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="description">説明</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea name="description[{{$frame_id}}]" class="form-control @if ($errors->has('description.$frame_id')) border-danger @endif" rows=2>{!!old("description.$frame_id")!!}</textarea>
                <small class="form-text text-muted">zipの場合は空になります。</small>
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
                    <label class="custom-control-label" for="is_cover{{$frame_id}}" id="label_is_cover{{$frame_id}}">チェックすると、アルバムの表紙に使われます。</label>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit" id="button_upload{{$frame->id}}">追加</button>
            <small id="upload-size-server-help" class="form-text text-muted">アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{UploadMaxSize::getDescription($photoalbum->image_upload_max_size)}}</span><br />保存時の幅、高さの最大px&nbsp;<span class="font-weight-bold">{{ResizedImageSize::getImageUploadResizeMessage($photoalbum->image_upload_max_px)}}</span></small>
        </div>
    </div>
</form>

{{-- 動画ファイルアップロードフォーム --}}
<form action="{{url('/')}}/redirect/plugin/photoalbums/uploadVideo/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("upload_video.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_video{{$frame->id}}">
        <div class="form-group row no-gutters mb-0">
            <label class="{{$frame->getSettingLabelClass()}} pr-3" for="upload_video">動画ファイル <label class="badge badge-danger">必須</label></label>
            <div class="custom-file {{$frame->getSettingInputClass()}}">
                <input type="hidden" name="upload_video[{{$frame_id}}]" value="">
                <input type="file" name="upload_video[{{$frame_id}}]" value="{{old("upload_video.$frame_id")}}" class="custom-file-input" id="upload_video{{$frame_id}}">
                <label class="custom-file-label ml-md-2 @if ($errors && $errors->has("upload_video.$frame_id")) border-danger @endif" for="upload_video" data-browse="参照">動画ファイル選択...</label>
            </div>
        </div>
        {{-- カスタムインプットで、ファイル行のマージン等が制御できないので、項目注釈やエラーを別の行で作成 --}}
        <div class="row">
            <label class="{{$frame->getSettingLabelClass()}} p-0"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                {{-- <small class="my-0 form-text text-muted">mp4, zip を許可します。zip の場合は展開されて登録されます。(zip は予定)</small> --}}
                <small class="my-0 form-text text-muted">mp4 を許可します。</small>
            </div>
        </div>
        @if ($errors && $errors->has("upload_video.$frame_id"))
        <div class="form-group row mb-0">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_video.*")}}</div>
            </div>
        </div>
        @endif

        <div class="form-group row no-gutters mb-0 mt-3">
            <label class="{{$frame->getSettingLabelClass()}} pr-3" for="upload_poster">ポスター画像</label>
            <div class="custom-file {{$frame->getSettingInputClass()}}">
                <input type="hidden" name="upload_poster[{{$frame_id}}]" value="">
                <input type="file" name="upload_poster[{{$frame_id}}]" value="{{old("upload_poster.$frame_id")}}" class="custom-file-input" id="upload_poster{{$frame_id}}">
                <label class="custom-file-label ml-md-2 @if ($errors && $errors->has("upload_poster.$frame_id")) border-danger @endif" for="upload_poster" data-browse="参照">ポスター画像選択...</label>
            </div>
        </div>
        {{-- カスタムインプットで、ファイル行のマージン等が制御できないので、項目注釈やエラーを別の行で作成 --}}
        <div class="row">
            <label class="{{$frame->getSettingLabelClass()}} p-0"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <small class="my-0 form-text text-muted">jpg, png, gif を許可します。</small>
            </div>
        </div>
        @if ($errors && $errors->has("upload_poster.$frame_id"))
        <div class="form-group row mb-0">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_poster.*")}}</div>
            </div>
        </div>
        @endif

        <div class="form-group row pt-3">
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
                        <input type="checkbox" name="is_cover[{{$frame_id}}]" value="1" class="custom-control-input" id="poster_is_cover{{$frame_id}}" disabled checked=checked>
                    @else
                        <input type="checkbox" name="is_cover[{{$frame_id}}]" value="1" class="custom-control-input" id="poster_is_cover{{$frame_id}}" disabled>
                    @endif
                    <label class="custom-control-label" for="poster_is_cover{{$frame_id}}" id="label_poster_is_cover{{$frame_id}}">チェックすると、ポスター画像がアルバムの表紙に使われます。</label>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_video{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit" id="button_upload_video{{$frame->id}}">追加</button>
            <small id="upload-size-server-help" class="form-text text-muted">アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{UploadMaxSize::getDescription($photoalbum->video_upload_max_size)}}</span></small>
        </div>
    </div>
</form>

{{-- 階層パンくず --}}
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

@if ($download_check)
<div class="bg-light p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame->id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>
@endif

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
