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
