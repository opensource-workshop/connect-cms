{{--
 * Connect-CMS マニュアルの問い合せページ
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category マニュアル生成
 --}}
{{-- CSS --}}
<style type="text/css">
/* テーブル */
.contact_table_css {
    border-collapse:  collapse;     /* セルの線を重ねる */
}
.contact_table_css th, .contact_table_css td {
    border: none;       /* 枠線指定 */
}
.contact_doc_th {
    background-color: #d0d0d0;      /* 背景色指定 */
}
</style>

<h2 style="text-align: center; font-size: 24px;">お問い合わせ</h2>

発行日　{{date('Y年m月d日')}}<br />
{!!$contact!!}
