{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}

{{-- defaultの初期表示blade --}}
@include('plugins.user.blogs.default.blogs', ['is_template_datafirst' => true])
