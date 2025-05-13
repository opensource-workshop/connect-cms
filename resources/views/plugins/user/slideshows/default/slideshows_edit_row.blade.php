{{--
 * 既存項目の行テンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
<tr>
    {{-- 表示順 --}}
    <td class="d-none d-lg-display d-lg-table-cell text-nowrap" style="text-align:center; vertical-align:middle;">
        {{-- 上移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $item->id }}, {{ $item->display_sequence }}, 'up')">
            <i class="fas fa-arrow-up"></i>
        </button>

        {{-- 下移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $item->id }}, {{ $item->display_sequence }}, 'down')">
            <i class="fas fa-arrow-down"></i>
        </button>
    </td>
    {{-- 表示フラグ--}}
    <td class="d-none d-lg-display d-lg-table-cell" style="text-align:center; vertical-align:middle;">
        <div class="custom-control custom-checkbox">
            <input
                type="checkbox"
                class="custom-control-input"
                name="display_flags[{{ $item->id }}]"
                value="{{ ShowType::show }}"
                id="display_flag_{{ $item->id }}"
                @if(isset($item->display_flag) && $item->display_flag == ShowType::show)
                    checked="checked"
                @endif
            >
            <label class="custom-control-label" for="display_flag_{{ $item->id }}"></label>
        </div>
    </td>
    {{-- 画像ファイル --}}
    <td class="d-block d-table-cell align-middle">
        {{-- 画像選択ボタン --}}
        <label class="input-group-btn d-flex align-items-center justify-content-center">
            <span class="btn btn-primary text-nowrap" style="cursor: hand; cursor:pointer;">
                画像選択<input type="file" name="image_files[{{ $item->id }}]" style="display:none" @change="setImageResource($event, {{ $item->id }})">
                <label class="badge badge-danger d-lg-none">必須</label>
            </span>
        </label>
        @include('plugins.common.errors_inline', ['name' => 'image_files.' . $item->id])

        <div class="d-flex align-items-center justify-content-center">
            {{-- 画像プレビュー --}}
            @if ($item->client_original_name)
                {{-- uploadレコードがあれば、モーダルのリンク表示 --}}
                <a href="#" data-toggle="modal" data-target="#modalPreviewRow{{ $item->id }}">
                    <img
                        :src="image_url_{{ $item->id }}"
                        width="100px"
                    >
            @else
                {{-- uploadレコードがなければ、no_image画像表示 --}}
                <img
                    src="{{ asset("images/core/no_image/no_image3.png") }}"
                    width="100px"
                >
            @endif
            </a>
        </div>
        {{-- 画像プレビューモーダル --}}
        <div class="modal fade" id="modalPreviewRow{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="modalPreviewRow{{ $item->id }}Title" aria-hidden="true">
            {{-- モーダルサイズはXL --}}
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    {{-- ヘッダ ※ファイル名をVueで表示 --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPreviewRow{{ $item->id }}Title">{{ $item->client_original_name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {{-- コンテンツ内容 --}}
                    <div class="modal-body">
                        <img
                            :src="image_url_{{ $item->id }}"
                            class="border img-fluid"
                        >
                    </div>
                </div>
            </div>
        </div>
    </td>
    {{-- リンクURL --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">リンクURL：</strong>
        <input
            type="text"
            name="link_urls[{{ $item->id }}]"
            class="form-control @if ($errors && $errors->has("link_urls.$item->id")) border-danger @endif"
            value="{{ old("link_urls.$item->id", $item->link_url) }}"
            placeholder="例：https://connect-cms.jp/"
        >
        @include('common.errors_inline', ['name' => "link_urls.$item->id"])
    </td>
    {{-- キャプション --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">キャプション：</strong>
        <input
            type="text"
            name="captions[{{ $item->id }}]"
            class="form-control @if ($errors && $errors->has("captions.$item->id")) border-danger @endif"
            value="{{ old("captions.$item->id", $item->caption) }}"
        >
        @include('common.errors_inline', ['name' => "captions.$item->id"])
    </td>
    {{-- リンクターゲット --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">リンクターゲット：</strong>
        <input
            type="text"
            name="link_targets[{{ $item->id }}]"
            class="form-control @if ($errors && $errors->has("link_targets.$item->id")) border-danger @endif"
            value="{{ old("link_targets.$item->id", $item->link_target) }}"
            placeholder="例：_blank、_self等"
        >
        @include('common.errors_inline', ['name' => "link_targets.$item->id"])
    </td>
    {{-- 削除ボタン --}}
    <td class="d-block d-lg-table-cell align-middle d-flex align-items-center justify-content-center">
        <button
            class="btn btn-danger cc-font-90 text-nowrap"
            onclick="javascript:return submit_delete_item({{ $item->id }});"
        >
            <i class="fas fa-trash-alt"></i> 削除
        </button>
    </td>
</tr>
