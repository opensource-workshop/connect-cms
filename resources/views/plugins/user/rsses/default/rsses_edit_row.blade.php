{{--
 * 既存項目の行テンプレート
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
--}}
<tr>
    {{-- 表示順 --}}
    <td class="d-none d-lg-display d-lg-table-cell text-nowrap" style="text-align:center; vertical-align:middle;">
        {{-- 上移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $url->id }}, {{ $url->display_sequence }}, 'up')">
            <i class="fas fa-arrow-up"></i>
        </button>

        {{-- 下移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $url->id }}, {{ $url->display_sequence }}, 'down')">
            <i class="fas fa-arrow-down"></i>
        </button>
    </td>
    {{-- 表示フラグ--}}
    <td class="d-none d-lg-display d-lg-table-cell" style="text-align:center; vertical-align:middle;">
        <div class="custom-control custom-checkbox">
            <input
                type="checkbox"
                class="custom-control-input"
                name="display_flags[{{ $url->id }}]"
                value="{{ ShowType::show }}"
                id="display_flag_{{ $url->id }}"
                @if(isset($url->display_flag) && $url->display_flag == ShowType::show)
                    checked="checked"
                @endif
            >
            <label class="custom-control-label" for="display_flag_{{ $url->id }}"></label>
        </div>
    </td>

    {{-- URL --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">URL：</strong>
        <input
            type="text"
            name="link_urls[{{ $url->id }}]"
            class="form-control @if ($errors && $errors->has("link_urls.$url->id")) border-danger @endif"
            value="{{ old("link_urls.$url->id", $url->url) }}"
            placeholder="例：https://connect-cms.jp/redirect/plugin/blogs/rss/2/5"
        >
        @include('common.errors_inline', ['name' => "link_urls.$url->id"])
    </td>
    {{-- タイトル --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">タイトル：</strong>
        <input
            type="text"
            name="titles[{{ $url->id }}]"
            class="form-control @if ($errors && $errors->has("titles.$url->id")) border-danger @endif"
            value="{{ old("titles.$url->id", $url->title) }}"
        >
        @include('common.errors_inline', ['name' => "titles.$url->id"])
    </td>
    {{-- キャプション --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">キャプション：</strong>
        <input
            type="text"
            name="captions[{{ $url->id }}]"
            class="form-control @if ($errors && $errors->has("captions.$url->id")) border-danger @endif"
            value="{{ old("captions.$url->id", $url->caption) }}"
        >
        @include('common.errors_inline', ['name' => "captions.$url->id"])
    </td>
    {{-- 取得数 --}}
    <td class="d-block d-lg-table-cell align-middle">
        <strong class="d-lg-none">取得数：</strong>
        <input
            type="number"
            name="item_counts[{{ $url->id }}]"
            class="form-control @if ($errors && $errors->has("item_counts.$url->id")) border-danger @endif"
            value="{{ old("item_counts.$url->id", $url->item_count) }}"
        >
        @include('common.errors_inline', ['name' => "item_counts.$url->id"])
    </td>

    {{-- 削除ボタン --}}
    <td class="d-block d-lg-table-cell align-middle d-flex align-urls-center justify-content-center">
        <button
            class="btn btn-danger cc-font-90 text-nowrap"
            onclick="javascript:return submit_delete_url({{ $url->id }});"
        >
            <i class="fas fa-trash-alt"></i> 削除
        </button>
    </td>
</tr>
