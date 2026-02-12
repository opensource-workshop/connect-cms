{{--
 * フォトアルバム画面テンプレート（フッタ）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
@if ($download_check)
<div class="bg-light mt-3 p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame_id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>
@endif
</form>
@can('posts.delete', [[null, $frame->plugin_name, $buckets]])
{{-- 削除確認モーダルウィンドウ --}}
<div class="modal" id="delete-confirm{{$frame_id}}" tabindex="-1" role="dialog" aria-labelledby="delete-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- ヘッダー --}}
            <div class="modal-header">
                <h5 class="modal-title" id="delete-title">削除確認</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- メインコンテンツ --}}
            <div class="modal-body">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="text-danger">以下のデータを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</div>
                        <ul class="text-danger" id="selected-contents{{$frame_id}}"></ul>
                    </div>
                    <div class="text-center mb-2">
                        {{-- キャンセルボタン --}}
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> キャンセル
                        </button>
                        {{-- 削除ボタン --}}
                        <button type="button" class="btn btn-danger" onclick="deleteContents{{$frame_id}}()"><i class="fas fa-check"></i> 本当に削除する</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
</div>
<script>
$(function () {
    $('.photoalbum-load-more').on('click', function () {
        var $button = $(this);
        if ($button.data('loading')) {
            return;
        }

        var containerSelector = $button.data('container');
        var rowSelector = $button.data('row');
        var statusSelector = $button.data('status');
        var target = $button.data('target');
        var label = $button.data('label') || $button.text();
        var $container = $(containerSelector);
        var $row = $(rowSelector);
        var $status = $(statusSelector);

        if (!$container.length || !$row.length) {
            return;
        }

        var offset = parseInt($container.data('offset'), 10) || 0;
        var limit = parseInt($container.data('limit'), 10) || 0;
        var total = parseInt($container.data('total'), 10) || 0;
        var url = $container.data('more-url');

        if (!url || offset >= total || limit <= 0) {
            return;
        }

        $button.data('loading', true).prop('disabled', true).text('読み込み中...');

        $.get(url, {
            target: target,
            offset: offset,
            limit: limit
        }).done(function (response) {
            if (response && response.html) {
                $row.append(response.html);
            }

            var responseTotal = total;
            if (response && response.total !== undefined && response.total !== null) {
                var parsedTotal = parseInt(response.total, 10);
                if (!isNaN(parsedTotal) && parsedTotal >= 0) {
                    responseTotal = parsedTotal;
                    $container.data('total', responseTotal);
                }
            }

            var nextOffset = offset;
            if (response && response.next_offset !== undefined && response.next_offset !== null) {
                var parsedOffset = parseInt(response.next_offset, 10);
                if (!isNaN(parsedOffset) && parsedOffset >= 0) {
                    nextOffset = parsedOffset;
                }
            }
            if (nextOffset > responseTotal) {
                nextOffset = responseTotal;
            }

            $container.data('offset', nextOffset);

            if ($status.length) {
                if (nextOffset >= responseTotal) {
                    $status.text('すべて表示しました');
                } else {
                    $status.text('表示中 ' + nextOffset + ' / ' + responseTotal);
                }
            }

            if (nextOffset >= responseTotal || nextOffset <= offset) {
                $button.hide();
            }
        }).fail(function () {
            if ($status.length) {
                $status.text('読み込みに失敗しました');
            }
        }).always(function () {
            $button.data('loading', false);
            if ($button.is(':visible')) {
                $button.prop('disabled', false).text(label);
            }
        });
    });
});
</script>
