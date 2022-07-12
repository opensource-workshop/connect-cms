{{--
 * ユーザ変更画面tabテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
@if (($function == "edit" || $function == "groups" || $function == "loginHistory") && $user->id)
    <nav class="p-1">
        <ul class="nav nav-tabs">
            <li role="presentation" class="nav-item">
                @if ($function == "edit")
                    <span class="nav-link active py-1">ユーザ変更</span>
                @else
                    <a href="{{url('/manage/user/edit')}}/{{$user->id}}" class="nav-link py-1">ユーザ変更</a></li>
                @endif
            </li>

            <li role="presentation" class="nav-item">
                @if ($function == "groups")
                    <span class="nav-link active py-1">グループ参加</span>
                @else
                    <a href="{{url('/manage/user/groups')}}/{{$user->id}}" class="nav-link py-1">グループ参加</a></li>
                @endif
            </li>

            <li role="presentation" class="nav-item">
                @if ($function == "loginHistory")
                    <span class="nav-link active py-1">ログイン履歴</span>
                @else
                    <a href="{{url('/manage/user/loginHistory')}}/{{$user->id}}" class="nav-link py-1">ログイン履歴</a></li>
                @endif
            </li>
        </ul>
    </nav>
@endif
