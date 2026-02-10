{{--
 * フォトアルバム画面テンプレート（画像・動画）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
{{-- データ一覧に画像が含まれる場合 --}}
@if ($photoalbum_contents->where('is_folder', 0)->isNotEmpty())
@php
if ($frame->isExpandNarrow()) {
    // 右・左エリア = スマホ表示と同等にする
    $col_class = 'col-12';
} else {
    // メインエリア・フッターエリア
    $col_class = 'col-md-4';
}

$play_view_default = \App\Enums\PhotoalbumPlayviewType::play_in_list;
$play_view = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::play_view, $play_view_default);
$description_list_length = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::description_list_length);
$image_modal_id = 'photoalbum-image-modal-' . $frame_id;
@endphp
<div class="row">
    @foreach($photoalbum_contents->where('is_folder', 0) as $photoalbum_content)
    @php
        $description_attr = str_replace(["\r\n", "\r", "\n"], '\\n', (string) $photoalbum_content->description);
    @endphp
    <div class="{{$col_class}}">
        <div class="card mt-3 shadow-sm">
        @if ($photoalbum_content->upload->is_image)
            {{-- 画像 --}}
            <img src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}?size=small"
                 id="photo_{{$frame_id}}_{{$loop->iteration}}"
                 style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid photoalbum-thumbnail"
                 data-toggle="modal"
                 data-target="#{{$image_modal_id}}"
                 data-thumb="{{url('/')}}/file/{{$photoalbum_content->upload_id}}?size=small"
                 data-full="{{url('/')}}/file/{{$photoalbum_content->upload_id}}"
                 data-title="{{e($photoalbum_content->name)}}"
                 data-description="{{e($description_attr)}}"
                 loading="lazy"
                 decoding="async"
            >
        @elseif ($photoalbum_content->isVideo($photoalbum_content->mimetype) && $play_view == PhotoalbumPlayviewType::play_in_detail)
            {{-- 動画：一覧はサムネイル画像のみで詳細画面で再生する --}}
            <a href="{{url('/')}}/plugin/photoalbums/detail/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}">
                <img src="{{url('/')}}/file/{{$photoalbum_content->poster_upload_id}}"
                     style="width: 100%; max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                     id="popup_photo_{{$frame_id}}_{{$loop->iteration}}"
                     class="img-fluid"
                     loading="lazy"
                     decoding="async"/>
            </a>
        @elseif ($photoalbum_content->isVideo($photoalbum_content->mimetype))
            {{-- 動画：一覧で再生する --}}
            <video controls controlsList="nodownload"
                 src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}"
                 id="video_{{$loop->iteration}}"
                 style="width: 100%; max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid"
                 @if ($photoalbum_content->poster_upload_id) poster="{{url('/')}}/file/{{$photoalbum_content->poster_upload_id}}" @endif
                 oncontextmenu="return false;"
            ></video>
        @endif
            <div class="card-body">
                <div class="d-flex">
                    @if ($download_check)
                        <div class="custom-control custom-checkbox d-inline">
                            <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->name}}">
                            <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
                        </div>
                    @endif
                    @if ($photoalbum_content->name)
                        @if ($photoalbum_content->isVideo($photoalbum_content->mimetype) && $play_view == PhotoalbumPlayviewType::play_in_detail)
                            <a href="{{url('/')}}/plugin/photoalbums/detail/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}">
                                <h5 class="card-title d-flex text-break">{{$photoalbum_content->name}}</h5>
                            </a>
                        @else
                            <h5 class="card-title d-flex text-break">{{$photoalbum_content->name}}</h5>
                        @endif
                    @endif
                </div>
                @if ($photoalbum_content->description)
                    <div class="card-text">
                    {{-- 一覧での説明文字数によって切り取って出力する --}}
                    @if ($photoalbum_content->isVideo($photoalbum_content->mimetype) &&
                         $play_view == PhotoalbumPlayviewType::play_in_detail &&
                         $description_list_length !== '' && $description_list_length < mb_strlen(strip_tags($photoalbum_content->description)))
                        {{ mb_substr(strip_tags($photoalbum_content->description), 0, $description_list_length) }}...
                    @else
                        {!!nl2br(e($photoalbum_content->description))!!}
                    @endif
                    </div>
                @endif
                {{-- 動画を一覧で再生する設定の場合は埋め込みコードを表示する--}}
                @if (($photoalbum_content->isVideo($photoalbum_content->mimetype)) &&
                      FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::embed_code) &&
                      $play_view == PhotoalbumPlayviewType::play_in_list)
                    <div class="card-text">
                        <a class="embed_code_check" data-name="embed_code{{$photoalbum_content->id}}" style="color: #007bff; cursor: pointer;" id="a_embed_code_check{{$photoalbum_content->id}}"><small>埋め込みコード</small> <i class="fas fa-caret-right"></i></a>
                        <input type="text" name="embed_code[{{$frame_id}}]" value='<iframe width="400" height="300" src="{{url('/')}}/download/plugin/photoalbums/embed/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}" frameborder="0" scrolling="no" allowfullscreen></iframe>' class="form-control" id="embed_code{{$photoalbum_content->id}}" style="display: none;">
                    </div>
                @endif
                @if (FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::posted_at, ShowType::not_show))
                    <div class="card-text"><small>登録日：{{$photoalbum_content->getUpdateOrCreatedAt('Y年n月j日')}}</small></div>
                @endif
                <div class="d-flex justify-content-between align-items-center">
                    @can('posts.update', [[$photoalbum_content, $frame->plugin_name, $buckets]])
                    <a href="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" class="btn btn-sm btn-success">
                        <i class="far fa-edit"></i> 編集
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="modal fade" id="{{$image_modal_id}}" tabindex="-1" role="dialog" aria-labelledby="{{$image_modal_id}}-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-middle">{{-- モーダルウィンドウの縦表示位置を調整・画像を大きく見せる --}}
        <div class="modal-content pb-3">
            <div class="modal-body mx-auto">
                {{-- 拡大表示ウィンドウにも、初期設定でサムネイルを設定しておき、クリック時に実寸画像を読み込みなおす --}}
                <img src="" style="object-fit: scale-down;" class="img-fluid photoalbum-modal-image"/>
            </div>
            <div class="modal-img_footer">
                <h5 class="card-title photoalbum-modal-title"></h5>
                <p class="card-text photoalbum-modal-description"></p>
                <button type="button" class="btn btn-success" data-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    var modalId = '#{{$image_modal_id}}';
    var $modal = $(modalId);
    $modal.on('show.bs.modal', function (event) {
        var $trigger = $(event.relatedTarget);
        var thumb = $trigger.data('thumb') || '';
        var full = $trigger.data('full') || '';
        var title = $trigger.data('title') || '';
        var description = $trigger.data('description') || '';

        $modal.find('.photoalbum-modal-image').attr('src', thumb);
        $modal.find('.photoalbum-modal-title').text(title);

        description = description.replace(/\\n/g, '\n');
        if (description) {
            var html = $('<div>').text(description).html().replace(/\n/g, '<br>');
            $modal.find('.photoalbum-modal-description').html(html);
        } else {
            $modal.find('.photoalbum-modal-description').html('');
        }

        if (full) {
            requestAnimationFrame(function () {
                $modal.find('.photoalbum-modal-image').attr('src', full);
            });
        }
    });

    $modal.on('hidden.bs.modal', function () {
        $modal.find('.photoalbum-modal-image').attr('src', '');
    });
});
</script>
@endif
