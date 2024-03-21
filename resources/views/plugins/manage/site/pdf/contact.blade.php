{{--
 * サイト管理（サイト設計書）のお問い合わせ先のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">お問い合わせ先</h2>

<br />
<h3><u>{{Configs::getConfigsValue($configs, 'document_support_org_title', null)}}</u></h3>
<br />
<br />
{!!nl2br(Configs::getConfigsValue($configs, 'document_support_org_txt', ''))!!}<br />

<h3><u>{{Configs::getConfigsValue($configs, 'document_support_contact_title', null)}}</u></h3>
<br />
<br />
{!!nl2br(Configs::getConfigsValue($configs, 'document_support_contact_txt', ''))!!}<br />

<h3><u>{{Configs::getConfigsValue($configs, 'document_support_other_title', null)}}</u></h3>
<br />
<br />
{!!nl2br(Configs::getConfigsValue($configs, 'document_support_other_txt', ''))!!}<br />
