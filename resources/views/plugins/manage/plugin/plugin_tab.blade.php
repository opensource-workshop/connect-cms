{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン管理
 --}}
<ul class="nav nav-tabs">
@if ($function == "index")
    <li class="nav-item"><a href="{{url('/manage/plugin')}}" class="nav-link active">プラグイン一覧</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/plugin')}}" class="nav-link">プラグイン一覧</a></li>
@endif
</ul>
