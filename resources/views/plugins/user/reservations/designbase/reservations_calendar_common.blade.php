{{--
 * 施設予約データ表示画面（月と週のラッパーテンプレート）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}

{{-- defaultの初期表示blade --}}
@include('plugins.user.reservations.default.reservations_calendar_common', ['is_template_designbase' => true])
