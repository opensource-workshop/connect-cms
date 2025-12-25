{{--
 * フォトアルバム・詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<script type="text/javascript">
    $(function () {
        // 埋め込みコードの表示
        $('#a_embed_code_check' + {{$photoalbum_content->id}}).on('click', function(){
            $("#" + $(this).data('name')).slideToggle();
            $("#" + $(this).data('name')).focus();
            $("#" + $(this).data('name')).select();
        });
    });
</script>

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

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

<div class="text-center">
    <div class="col-md">
        <div class="mb-1">
            <video controls controlsList="nodownload"
                 src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}"
                 id="video"
                 style="max-height: 600px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid"
                 @if ($photoalbum_content->poster_upload_id) poster="{{url('/')}}/file/{{$photoalbum_content->poster_upload_id}}" @endif
                 oncontextmenu="return false;"
            ></video>
        </div>
    </div>

    @if ($photoalbum_content->description)
        <div class="card-text text-left">{!!nl2br(e($photoalbum_content->description))!!}</div>
    @endif

    @if (($photoalbum_content->isVideo($photoalbum_content->mimetype)) && FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::embed_code))
        <div class="card-text text-left">
            <a class="embed_code_check" data-name="embed_code{{$photoalbum_content->id}}" style="color: #007bff; cursor: pointer;" id="a_embed_code_check{{$photoalbum_content->id}}"><small>埋め込みコード</small> <i class="fas fa-caret-right"></i></a>
            <input type="text" name="embed_code[{{$frame_id}}]" value='<iframe width="400" height="300" src="{{url('/')}}/download/plugin/photoalbums/embed/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}" frameborder="0" scrolling="no" allowfullscreen></iframe>' class="form-control" id="embed_code{{$photoalbum_content->id}}" style="display: none;">
        </div>
    @endif
</div>

@endsection
