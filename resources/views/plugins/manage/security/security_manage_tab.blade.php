{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category セキュリティ管理
 --}}
<ul class="nav nav-tabs">
@if ($function == "index")
    <li class="nav-item"><a href="{{url('/manage/security')}}" class="nav-link active">ログイン制限</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/security')}}" class="nav-link">ログイン制限</a></li>
@endif
</ul>
