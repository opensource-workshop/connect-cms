{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
<ul class="nav nav-tabs">
@if ($function == "index")
    <li class="nav-item"><a href="{{url('/manage/user')}}" class="nav-link active">ユーザ一覧</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/user')}}" class="nav-link">ユーザ一覧</a></li>
@endif

@if ($function == "regist")
    <li class="nav-item"><a href="{{url('/manage/user/regist')}}" class="nav-link active">ユーザ登録</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/user/regist')}}" class="nav-link">ユーザ登録</a></li>
@endif

@if ($function == "edit")
    <li class="nav-item"><a class="nav-link active">ユーザ変更</a></li>
@endif

</ul>
