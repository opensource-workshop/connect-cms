{{--
 * ブログ記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}

{{-- defaultの詳細表示blade --}}
@include('plugins.user.blogs.default.blogs_show', ['is_template_datafirst' => true])
