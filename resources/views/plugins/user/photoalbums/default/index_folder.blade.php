{{--
 * フォトアルバム画面テンプレート（フォルダ）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
{{-- データ一覧にアルバムが含まれる場合 --}}
@php
    $photoalbum_folder_items = $photoalbum_folder_items ?? $photoalbum_contents->where('is_folder', 1)->values();
    $photoalbum_folder_total = $photoalbum_folder_total ?? $photoalbum_folder_items->count();
    $photoalbum_folder_offset = $photoalbum_folder_offset ?? $photoalbum_folder_items->count();
    $photoalbum_folder_limit = $photoalbum_folder_limit ?? $photoalbum_folder_total;
    $photoalbum_load_more_use = $photoalbum_load_more_use ?? \App\Enums\UseType::not_use;
    $folder_list_id = 'photoalbum-folder-list-' . $frame_id;
    $folder_row_id = 'photoalbum-folder-row-' . $frame_id;
@endphp
@if ($photoalbum_folder_total > 0)
<div id="{{$folder_list_id}}"
     data-more-url="{{url('/')}}/json/photoalbums/moreContents/{{$page->id}}/{{$frame_id}}/{{$parent_id ?? 0}}"
     data-offset="{{$photoalbum_folder_offset}}"
     data-limit="{{$photoalbum_folder_limit}}"
     data-total="{{$photoalbum_folder_total}}">
    <div class="row" id="{{$folder_row_id}}">
        @include('plugins.user.photoalbums.default.index_folder_items', ['photoalbum_folder_items' => $photoalbum_folder_items])
    </div>
</div>
@if ($photoalbum_load_more_use == \App\Enums\UseType::use && $photoalbum_folder_total > $photoalbum_folder_offset)
    <div class="text-center mt-3 photoalbum-load-more-wrap">
        <button type="button"
                class="btn btn-outline-secondary photoalbum-load-more"
                data-target="folder"
                data-container="#{{$folder_list_id}}"
                data-row="#{{$folder_row_id}}"
                data-status="#photoalbum-folder-status-{{$frame_id}}"
                data-label="フォルダをもっと見る">
            フォルダをもっと見る
        </button>
        <div id="photoalbum-folder-status-{{$frame_id}}" class="small text-muted mt-1">
            表示中 {{$photoalbum_folder_offset}} / {{$photoalbum_folder_total}}
        </div>
    </div>
@endif
@endif
