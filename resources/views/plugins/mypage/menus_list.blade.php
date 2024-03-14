{{--
 * マイページメニューリスト
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category マイページ
--}}
<div class="list-group">
    @if (isset($plugin_name) && $plugin_name == 'index')
        <a href="{{url('/')}}/mypage" class="list-group-item active">プロフィール</a>
    @else
        <a href="{{url('/')}}/mypage" class="list-group-item">プロフィール</a>
    @endif
    @if (isset($plugin_name) && $plugin_name == 'profile')
        <a href="{{url('/')}}/mypage/profile" class="list-group-item active">プロフィール変更</a>
    @else
        <a href="{{url('/')}}/mypage/profile" class="list-group-item">プロフィール変更</a>
    @endif
    @if (isset($plugin_name) && $plugin_name == 'loginhistory')
        <a href="{{url('/')}}/mypage/loginHistory" class="list-group-item active">ログイン履歴</a>
    @else
        <a href="{{url('/')}}/mypage/loginHistory" class="list-group-item">ログイン履歴</a>
    @endif
</div>
