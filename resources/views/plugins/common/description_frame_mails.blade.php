{{--
 * 共通のメール設定画面の説明-設定blade
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@php
    $use_title = false;
    $use_body = false;
@endphp

@include('plugins.common.description_frame_mails_common', ['embedded_tags' => NoticeEmbeddedTag::getDescriptionEmbeddedTags($use_title, $use_body)])
