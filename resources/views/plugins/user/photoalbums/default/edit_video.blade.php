{{--
 * フォトアルバム・編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

<style>
.modal-middle {        //モーダルウィンドウの縦表示位置を調整
    margin: 5% auto;
}

.modal-img_footer {    //表示予定のテキストとボタンを中央揃え
    padding: .5em;
    text-align: center;
}
</style>

<script type="text/javascript">
    $(function () {
        @can('posts.create', [[null, $frame->plugin_name, $buckets]])
        $('.custom-file-input').on('change',function(){
            $(this).next('.custom-file-label').html($(this)[0].files[0].name);
        });
        @endcan
    });
</script>

{{-- 投稿用フォーム --}}
<form action="{{url('/')}}/redirect/plugin/photoalbums/editVideo/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_post{{$frame_id}}" enctype="multipart/form-data">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame_id}}">
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right" for="upload_video">動画ファイル</label>
        <div class="col-md-10">

            <div class="mb-1">
                <video controls controlsList="nodownload"
                     src="/file/{{$photoalbum_content->upload_id}}"
                     id="video"
                     style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                     class="img-fluid"
                     @if ($photoalbum_content->poster_upload_id) poster="/file/{{$photoalbum_content->poster_upload_id}}" @endif
                     oncontextmenu="return false;"
                ></video>
            </div>
            <div class="custom-file">
                <input type="hidden" name="upload_video[{{$frame_id}}]" value="">
                <input type="file" name="upload_video[{{$frame_id}}]" value="{{old("upload_video.$frame_id")}}" class="custom-file-input @if ($errors && $errors->has("upload_video.$frame_id")) border-danger @endif" id="upload_video{{$frame_id}}">
                <label class="custom-file-label" for="upload_video" data-browse="参照">動画ファイル選択...</label>
                <small class="form-text text-muted">ファイルを入れ替える際は指定します。</small>
                @if ($errors && $errors->has("upload_video.$frame_id")) 
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_video.*")}}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right" for="upload_poster">ポスター画像</label>
        <div class="col-md-10">
            @if ($photoalbum_content->poster_upload_id)
            <div class="mb-1">
                <img src="/file/{{$photoalbum_content->poster_upload_id}}?size=small"
                     id="poster"
                     style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                     class="img-fluid"
                >
            </div>
            @endif
            <div class="custom-file">
                <input type="hidden" name="upload_poster[{{$frame_id}}]" value="">
                <input type="file" name="upload_poster[{{$frame_id}}]" value="{{old("upload_poster.$frame_id")}}" class="custom-file-input @if ($errors && $errors->has("upload_poster.$frame_id")) border-danger @endif" id="upload_poster{{$frame_id}}">
                <label class="custom-file-label" for="upload_poster" data-browse="参照">ポスター画像選択...</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right" for="title">タイトル</label>
        <div class="col-md-10">
            <input type="text" name="title[{{$frame_id}}]" value="{{old("title.$frame_id", $photoalbum_content->name)}}" class="form-control @if ($errors->has("title.$frame_id")) border-danger @endif" id="title{{$frame_id}}">
            <small class="form-text text-muted">空の場合、ファイル名をタイトルとして登録します。</small>
            @if ($errors && $errors->has("title.$frame_id")) 
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("title.*")}}</div>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">説明</label>
        <div class="col-md-10">
            <textarea name="description[{{$frame_id}}]" class="form-control @if ($errors->has("description.$frame_id")) border-danger @endif" id="description{{$frame_id}}" rows=2>{!!old("description.$frame_id", $photoalbum_content->description)!!}</textarea>
            @if ($errors && $errors->has("description.$frame_id")) 
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("description.*")}}</div>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">アルバム表紙</label>
        <div class="col-md-10">
            <div class="custom-control custom-checkbox">
                @if(old("is_cover.$frame_id", $photoalbum_content->is_cover))
                    <input type="checkbox" name="is_cover[{{$frame_id}}]" value="1" class="custom-control-input" id="is_cover{{$frame_id}}" checked=checked>
                @else
                    <input type="checkbox" name="is_cover[{{$frame_id}}]" value="1" class="custom-control-input" id="is_cover{{$frame_id}}">
                @endif
                <label class="custom-control-label" for="is_cover{{$frame_id}}">チェックすると、アルバムの表紙に使われます。</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($photoalbum_content->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->parent_id}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <input type="hidden" name="bucket_id[{{$frame_id}}]" value="">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
                </div>
            </div>
{{--
            @if (!empty($photoalbum_content->id))
            <div class="col-3 col-xl-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$frame_id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                </a>
            </div>
            @endif
--}}
        </div>
    </div>
</form>

<div id="collapse{{$frame_id}}" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/photoalbums/delete/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" name="redirect_path" value="{{$page->permanent_link}}#frame-{{$frame_id}}">
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
