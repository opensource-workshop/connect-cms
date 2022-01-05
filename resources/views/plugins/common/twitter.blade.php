{{--
 * Twitter アイコンテンプレート
 *
 * @param $post_title (プラグインで項目名が異なることがあるため、あえて明示的変数にしています)
 *
 * // 暗黙で利用
 * @param $frame
 * @param $frame_configs
 * @param $page
 * @param $post (インクルード元と名前が異なる場合は、インクルード元から名前指定の引数で渡すようにしてください)
--}}
@if (FrameConfig::getConfigValueAndOld($frame_configs, BlogFrameConfig::blog_display_twitter_button) == ShowType::show)
<a class="btn btn-sm btn-link btn-light border"
   href="javascript:void window.open('{{urlencode("http://twitter.com/intent/tweet?text=$post_title ")}}{{urlencode(url("/plugin/blogs/show/$page->id/$frame_id/$post->id"))}}','_blank');">
   <h6 class="d-inline"><i class="fab fa-twitter"></i></h6>
</a>
@endif
