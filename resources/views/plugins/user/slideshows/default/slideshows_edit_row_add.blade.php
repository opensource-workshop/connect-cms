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
                画像選択<input type="file" name="image_file" style="display:none" @change="setImageResource('add', arguments[0])">
                <label class="badge badge-danger d-xl-none">必須</label>
            </span>
        </label>
        @include('plugins.common.errors_inline', ['name' => 'image_file'])

        <div v-if="image_url_add" class="d-flex align-items-center justify-content-center">
            {{-- 画像プレビュー --}}
            <a href="#" data-toggle="modal" data-target="#modalPreviewAdd">
                <img
                    :src="image_url_add"
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
            <div class="modal-dialog modal-dialog-centered" role="document">
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
                            :src="image_url_add"
                            class="border img-fluid"
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
        @include('common.errors_inline', ['name' => 'link_url'])
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
        @include('common.errors_inline', ['name' => 'caption'])
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
        @include('common.errors_inline', ['name' => 'link_target'])
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

{{-- PDF選択 --}}

@if (!empty(config('connect.PDF_THUMBNAIL_API_URL')))
<tr>
    <td class="d-none d-xl-display d-xl-table-cell"></td>
    <td class="d-none d-xl-display d-xl-table-cell"></td>
    <td class="d-block d-xl-table-cell align-middle">
        <label class="input-group-btn d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalPdfAdd">
            <span class="btn btn-primary text-nowrap" style="cursor: hand; cursor:pointer;">
                PDF選択
            </span>
        </label>
        <div v-if="selected_pdf">@{{ selected_pdf }}</div>
        @include('plugins.common.errors_inline', ['name' => 'pdf_file'])
        @include('plugins.common.errors_inline', ['name' => 'pdf_image_size'])
        @include('plugins.common.errors_inline', ['name' => 'pdf_password'])
        {{-- PDF選択モーダル --}}
        <div class="modal fade" id="modalPdfAdd" tabindex="-1" role="dialog" aria-labelledby="modalPdfAddTitle" aria-hidden="true">
            {{-- モーダルサイズはXL --}}
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    {{-- ヘッダ --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPdfAddTitle">PDF選択</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    {{-- コンテンツ内容 --}}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="pdf_file">ファイル</label>
                            <input type="file" class="form-control-file" id="pdf_file" name="pdf_file" accept=".pdf" @change="setPdfFile(arguments[0])">
                            <small id="upload-size-server-help" class="form-text text-muted">アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{ini_get('upload_max_filesize')}}</span></small>
                        </div>
                        <div class="form-group">
                            <label for="pdf_image_size">画像の大きさ</label>
                            <select id="pdf_image_size" class="form-control" name="pdf_image_size">
                                @foreach (WidthOfPdfThumbnail::getMembers() as $enum_value => $enum_label)
                                    <option value="{{$enum_value}}" @if (old('pdf_image_size') == $enum_value) selected @endif>{{$enum_label}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pdf_password">PDFパスワード</label>
                            <input type="text" class="form-control" id="pdf_password" name="pdf_password" value="{{ old('pdf_password') }}">
                            <small class="form-text text-muted">PDFにパスワードが設定されている場合に入力してください。</small>
                        </div>
                    </div>
                    {{-- フッター --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">保存</button>
                    </div>
                </div>
            </div>
        </div>
    </td>
    {{-- リンクURL --}}
    <td class="d-block d-xl-table-cell align-middle"></td>
    {{-- キャプション --}}
    <td class="d-block d-xl-table-cell align-middle"></td>
    {{-- リンクターゲット --}}
    <td class="d-block d-xl-table-cell align-middle"></td>
    {{-- ＋ボタン --}}
    <td class="d-block d-xl-table-cell align-middle d-flex align-items-center justify-content-center">
        <button
            class="btn btn-success cc-font-90 text-nowrap"
            onclick="javascript:submit_add_pdf();"
        >
            <i class="fas fa-plus"></i> 追加
        </button>
    </td>
</tr>
@endisset
