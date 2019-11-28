{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category システム管理
 --}}
<ul class="nav nav-tabs">
@if ($function == "index")
    <li class="nav-item"><a href="{{url('/manage/system')}}" class="nav-link active">デバックモード</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/system')}}" class="nav-link">デバックモード</a></li>
@endif
</ul>
