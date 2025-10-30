{{--
 * ブログ記事の状態バッジ（承認待ち・一時保存）表示用の共通部品
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
{{-- 承認待ち --}}
@if ($post->status == 2)
    @can('role_update_or_approval',[[$post, $frame->plugin_name, $buckets]])
        <span class="badge badge-warning align-bottom">承認待ち</span>
    @endcan
@endif
{{-- 一時保存 --}}
@can('posts.update',[[$post, $frame->plugin_name, $buckets]])
    @if ($post->status == 1)
        <span class="badge badge-warning align-bottom">一時保存</span>
    @endif
@endcan
