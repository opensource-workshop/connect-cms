{{--
 * 項目の追加行テンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
<tr>
    <td class="d-none d-xl-display d-xl-table-cell"></td>
    <td class="d-none d-xl-display d-xl-table-cell"></td>
    {{-- 画像ファイル --}}
    <td class="d-block d-xl-table-cell align-middle">
        {{-- 画像選択ボタン --}}
        <label class="input-group-btn d-flex align-items-center justify-content-center">
            <span class="btn btn-primary text-nowrap" style="cursor: hand; cursor:pointer;">
                画像選択<input type="file" name="image_file" style="display:none" @change="selectFile">
                <label class="badge badge-danger d-xl-none">必須</label>
            </span>
        </label>
        @include('common.errors_inline', ['name' => 'image_file'])

        <div v-if="tmp_image_url_add" class="d-flex align-items-center justify-content-center">
            {{-- 画像プレビュー --}}
            <a href="#" data-toggle="modal" data-target="#modalPreviewAdd">
                <img 
                    :src="tmp_image_url_add" 
                    class="border"
                    width="100px" 
                    data-toggle="tooltip"
                    :title="file_name_add"
                >
            </a>
        </div>
        {{-- 画像プレビューモーダル --}}
        <div class="modal fade" id="modalPreviewAdd" tabindex="-1" role="dialog" aria-labelledby="modalPreviewAddTitle" aria-hidden="true">
            {{-- モーダルサイズはXL --}}
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    {{-- ヘッダ ※ファイル名をVueで表示 --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPreviewAddTitle">@{{ file_name_add }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {{-- コンテンツ内容 --}}
                    <div class="modal-body">
                        <img 
                            :src="tmp_image_url_add" 
                            class="border"
                            width="100%" 
                        >
                    </div>
                </div>
            </div>
        </div>
    </td>
    {{-- リンクURL --}}
    <td class="d-block d-xl-table-cell align-middle">
        <strong class="d-xl-none">リンクURL：</strong>
        <input 
            type="text" 
            name="link_url" 
            class="form-control @if ($errors && $errors->has('link_url')) border-danger @endif" 
            value="{{ old('link_url') }}"
            placeholder="例：https://connect-cms.jp/"
        >
    </td>
    {{-- キャプション --}}
    <td class="d-block d-xl-table-cell align-middle">
        <strong class="d-xl-none">キャプション：</strong>
        <input 
            type="text" 
            name="caption" 
            class="form-control @if ($errors && $errors->has('caption')) border-danger @endif" 
            value="{{ old('caption') }}"
        >
    </td>
    {{-- リンクターゲット --}}
    <td class="d-block d-xl-table-cell align-middle">
        <strong class="d-xl-none">リンクターゲット：</strong>
        <input 
            type="text" 
            name="link_target" 
            class="form-control @if ($errors && $errors->has('link_target')) border-danger @endif" 
            value="{{ old('link_target') }}"
            placeholder="例：_blank、_self等"
        >
    </td>
    {{-- ＋ボタン --}}
    <td class="d-block d-xl-table-cell align-middle d-flex align-items-center justify-content-center">
        <button 
            class="btn btn-success cc-font-90 text-nowrap" 
            onclick="javascript:submit_add_item();"
        >
            <i class="fas fa-plus"></i> 追加
        </button>
    </td>
</tr>