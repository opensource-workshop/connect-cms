{{--
 * メール設定画面の説明-設定blade
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
 *
 * copy by resources\views\plugins\common\description_frame_mails.blade.php
--}}
@php
    $use_title = true;
    $use_body = true;
@endphp

@include('plugins.common.description_frame_mails_common', ['embedded_tags' => NoticeEmbeddedTag::getDescriptionEmbeddedTags($use_title, $use_body)])
