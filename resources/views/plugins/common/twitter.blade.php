{{--
 * Twitter アイコンテンプレート
 *
 * @param $post_title (プラグインで項目名が異なることがあるため、あえて明示的変数にしています)
 * @param $share_connect_url
 * @param $frame_config_name 取得するFrameConfigの設定名
 *
 * // 暗黙で利用
 * @param $frame_configs
--}}
@if (FrameConfig::getConfigValueAndOld($frame_configs, $frame_config_name) == ShowType::show)
<a class="btn btn-sm btn-link btn-light border"
   href="javascript:void window.open('{{urlencode("http://twitter.com/intent/tweet?text=$post_title ")}}{{urlencode(url($share_connect_url))}}','_blank');">
   <h6 class="d-inline"><i class="fab fa-twitter"></i></h6>
</a>
@endif
