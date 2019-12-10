{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
<ul class="nav nav-tabs">
@if ($function == "index")
    <li class="nav-item"><a href="{{url('/manage/page')}}" class="nav-link active">ページ追加・一覧</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/page')}}" class="nav-link">ページ追加・一覧</a></li>
@endif
@if ($function == "import")
    <li class="nav-item"><a href="{{url('/manage/page/import')}}" class="nav-link active">CSVインポート</a></li>
@else
    <li class="nav-item"><a href="{{url('/manage/page/import')}}" class="nav-link">CSVインポート</a></li>
@endif
</ul>
