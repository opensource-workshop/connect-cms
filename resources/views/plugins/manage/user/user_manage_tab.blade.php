{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
<ul class="nav nav-tabs">
@if ($function == "index")
    <li class="active"><a href="{{url('/manage/user')}}" style="background-color: #ffffff;">ユーザ一覧</a></li>
@else
    <li><a href="{{url('/manage/user')}}">ユーザ一覧</a></li>
@endif

@if ($function == "regist")
    <li class="active"><a href="{{url('/manage/user/regist')}}" style="background-color: #ffffff;">ユーザ登録</a></li>
@else
    <li><a href="{{url('/manage/user/regist')}}">ユーザ登録</a></li>
@endif

@if ($function == "edit")
    <li class="active"><a style="background-color: #ffffff;">ユーザ変更</a></li>
@endif

</ul>
