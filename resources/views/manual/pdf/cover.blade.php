{{--
 * Connect-CMS マニュアルの表紙
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category マニュアル生成
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<h1 style="text-align: center; font-size: 32px;">Connect-CMS マニュアル</h1>
<h3 style="text-align: center; font-size: 18px;">https://connect-cms.jp</h3>
<h4 style="text-align: center; font-size: 12px;">Connect-CMSのバージョン：{{config('version.cc_version')}}</h4>
<h4 style="text-align: center; font-size: 12px;">{{date('Y年m月d日')}}</h4>
<h1></h1>
@if ($category == 'manage')
    <h1 style="text-align: center; font-size: 32px;">管理者編</h1>
@elseif ($category == 'user')
    <h1 style="text-align: center; font-size: 32px;">一般ユーザ編</h1>
@elseif ($category == 'study')
    <h1 style="text-align: center; font-size: 32px;">Connect-Study編</h1>
@endif

@if ($level == 'basic')
    <h1 style="text-align: center; font-size: 28px;">基本機能版マニュアル</h1>
    <div style="text-align: center;">全機能の掲載されたマニュアルは以下のConnect-CMSマニュアルサイトよりダウンロードしてください。<br />
    https://manual.connect-cms.jp/</div>
@endif

<h1></h1>
<h1></h1>
<h1></h1>
<h1></h1>
<h1></h1>
<h1></h1>
<h1></h1>
<h1></h1>
<h4 style="text-align: center; font-size: 9px;">
Connect-CMS マニュアル は株式会社オープンソース・ワークショップと協力者ページの人々によって記載されました。
</h4>
