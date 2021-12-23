{{--
 * サイト管理（サイト設計書）のサイト基本書表紙のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
<style type="text/css">
/* テーブル */
.table_css {
    border-collapse:  collapse;     /* セルの線を重ねる */
}
.table_css th, .table_css td {
    border: 0.1px solid #000;       /* 枠線指定 */
}
.doc_th {
    background-color: #d0d0d0;      /* 背景色指定 */
}
</style>

<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<h1 style="text-align: center; font-size: 32px;">Webサイト設計書</h1>
<h2 style="text-align: center; font-size: 24px;">{{$configs->firstWhere('name', 'base_site_name')->value}}</h2>
<h3 style="text-align: center; font-size: 18px;">{{url('/')}}</h3>
<h4 style="text-align: center; font-size: 12px;">{{date('Y年m月d日')}}</h4>


