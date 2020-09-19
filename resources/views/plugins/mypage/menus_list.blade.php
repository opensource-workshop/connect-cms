{{--
    マイページメニューリスト
--}}

<div class="list-group">
    @if (isset($plugin_name) && $plugin_name == 'index')
        <a href="{{url('/')}}/mypage" class="list-group-item active">マイページ</a>
    @else
        <a href="{{url('/')}}/mypage" class="list-group-item">マイページ</a>
    @endif
    @if (isset($plugin_name) && $plugin_name == 'profile')
        <a href="{{url('/')}}/mypage/profile" class="list-group-item active">プロフィール</a>
    @else
        <a href="{{url('/')}}/mypage/profile" class="list-group-item">プロフィール</a>
    @endif
</div>
