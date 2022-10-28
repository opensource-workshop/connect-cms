{{--
 * 掲示板画面テンプレート。
 *
 * @author <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}

{{-- defaultのスレッド表示blade --}}
@include('plugins.user.bbses.default.thread_show', ['is_template_no_frame' => true])