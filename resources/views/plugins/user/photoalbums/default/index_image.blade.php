{{--
 * フォトアルバム画面テンプレート（画像・動画）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
{{-- データ一覧に画像が含まれる場合 --}}
@php
    $photoalbum_image_items = $photoalbum_image_items ?? (($photoalbum_contents ?? collect())->where('is_folder', 0)->values());
    $photoalbum_image_total = $photoalbum_image_total ?? $photoalbum_image_items->count();
    $photoalbum_image_offset = $photoalbum_image_offset ?? $photoalbum_image_items->count();
    $photoalbum_image_limit = $photoalbum_image_limit ?? $photoalbum_image_total;
    $photoalbum_load_more_use = $photoalbum_load_more_use ?? \App\Enums\UseType::not_use;
    $image_modal_id = 'photoalbum-image-modal-' . $frame_id;
    $image_list_id = 'photoalbum-image-list-' . $frame_id;
    $image_row_id = 'photoalbum-image-row-' . $frame_id;
@endphp
@if ($photoalbum_image_total > 0)
<div id="{{$image_list_id}}"
     data-more-url="{{url('/')}}/json/photoalbums/moreContents/{{$page->id}}/{{$frame_id}}/{{$parent_id ?? 0}}"
     data-offset="{{$photoalbum_image_offset}}"
     data-limit="{{$photoalbum_image_limit}}"
     data-total="{{$photoalbum_image_total}}">
    <div class="row" id="{{$image_row_id}}">
        @include('plugins.user.photoalbums.default.index_image_items', ['photoalbum_image_items' => $photoalbum_image_items, 'image_modal_id' => $image_modal_id])
    </div>
</div>
@if ($photoalbum_load_more_use == \App\Enums\UseType::use && $photoalbum_image_total > $photoalbum_image_offset)
    <div class="text-center mt-3 photoalbum-load-more-wrap">
        <button type="button"
                class="btn btn-outline-secondary photoalbum-load-more"
                data-target="image"
                data-container="#{{$image_list_id}}"
                data-row="#{{$image_row_id}}"
                data-status="#photoalbum-image-status-{{$frame_id}}"
                data-label="画像をもっと見る">
            画像をもっと見る
        </button>
        <div id="photoalbum-image-status-{{$frame_id}}" class="small text-muted mt-1">
            表示中 {{$photoalbum_image_offset}} / {{$photoalbum_image_total}}
        </div>
    </div>
@endif

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
    var $htmlDecoder = $('<textarea />');
    var decodeHtmlEntities = function (value) {
        if (!value) {
            return '';
        }
        return $htmlDecoder.html(value).text();
    };
    $modal.on('show.bs.modal', function (event) {
        var $trigger = $(event.relatedTarget);
        var thumb = $trigger.data('thumb') || '';
        var full = $trigger.data('full') || '';
        var title = $trigger.data('title') || '';
        var description = $trigger.attr('data-description') || '';

        $modal.find('.photoalbum-modal-image').attr('src', thumb);
        $modal.find('.photoalbum-modal-title').text(title);

        description = decodeHtmlEntities(description.replace(/\\n/g, '\n'));
        var $description = $modal.find('.photoalbum-modal-description').empty();
        if (description) {
            var lines = description.split('\n');
            $.each(lines, function (index, line) {
                if (index) {
                    $description.append('<br>');
                }
                $description.append(document.createTextNode(line));
            });
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
