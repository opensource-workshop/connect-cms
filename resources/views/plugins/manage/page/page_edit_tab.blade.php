{{--
 * ページ変更画面tabテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
@if (($function == "edit" || $function == "role" || $function == "migrationOrder") && $page->id)
    <nav class="p-1">
        <ul class="nav nav-tabs">
            <li role="presentation" class="nav-item">
                @if ($function == "edit")
                    <span class="nav-link active py-1">ページ変更</span>
                @else
                    <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="nav-link py-1">ページ変更</a></li>
                @endif
            </li>

            <li role="presentation" class="nav-item">
                @if ($function == "role")
                    <span class="nav-link active py-1">ページ権限設定</span>
                @else
                    <a href="{{url('/manage/page/role')}}/{{$page->id}}" class="nav-link py-1">ページ権限設定</a></li>
                @endif
            </li>

            <li role="presentation" class="nav-item">
                @if ($function == "migrationOrder")
                    <span class="nav-link active py-1">外部ページインポート</span>
                @else
                    <a href="{{url('/manage/page/migrationOrder')}}/{{$page->id}}" class="nav-link py-1">外部ページインポート</a></li>
                @endif
            </li>
        </ul>
    </nav>
@endif
