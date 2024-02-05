{{--
 * 項目の追加行テンプレート
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
--}}
<tr>
    <td class="d-none d-lg-display d-lg-table-cell"></td>
    <td class="d-none d-lg-display d-lg-table-cell"></td>

    {{-- 取得元URL --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">取得元URL：</strong>
        <input
            type="text"
            name="url"
            class="form-control @if ($errors && $errors->has('url')) border-danger @endif"
            value="{{ old('url') }}"
            placeholder="例：https://connect-cms.jp/redirect/plugin/blogs/rss/2/5"
        >
        @include('common.errors_inline', ['name' => 'url'])
    </td>
    {{-- タイトル --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">タイトル</strong>
        <input
            type="text"
            name="title"
            class="form-control @if ($errors && $errors->has('title')) border-danger @endif"
            value="{{ old('title') }}"
        >
        @include('common.errors_inline', ['name' => 'caption'])
    </td>
    {{-- キャプション --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">キャプション：</strong>
        <input
            type="text"
            name="caption"
            class="form-control @if ($errors && $errors->has('caption')) border-danger @endif"
            value="{{ old('caption') }}"
        >
        @include('common.errors_inline', ['name' => 'caption'])
    </td>
    {{-- 取得数 --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">取得数：</strong>
        <input
            type="number"
            name="item_count"
            class="form-control @if ($errors && $errors->has('item_count')) border-danger @endif"
            value="{{ old('item_count', '10') }}"
        >
        @include('common.errors_inline', ['name' => 'item_count'])
    </td>
    

    {{-- ＋ボタン --}}
    <td class="d-block d-lg-table-cell align-middle d-flex align-urls-center justify-content-center">
        <button
            class="btn btn-success cc-font-90 text-nowrap"
            onclick="javascript:submit_add_url();"
        >
            <i class="fas fa-plus"></i> 追加
        </button>
    </td>
</tr>

{{-- PDF選択 --}}

