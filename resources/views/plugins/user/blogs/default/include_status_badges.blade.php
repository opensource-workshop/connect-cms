{{--
 * ブログ記事の状態バッジ（承認待ち・一時保存）表示用の共通部品
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
{{-- 承認待ち --}}
@if ($post->status == StatusType::approval_pending)
    @can('role_update_or_approval',[[$post, $frame->plugin_name, $buckets]])
        <span class="badge badge-warning align-bottom">承認待ち</span>
    @endcan
@endif
{{-- 一時保存 --}}
@can('posts.update',[[$post, $frame->plugin_name, $buckets]])
    @if ($post->status == StatusType::temporary)
        <span class="badge badge-warning align-bottom">一時保存</span>
    @endif
@endcan
