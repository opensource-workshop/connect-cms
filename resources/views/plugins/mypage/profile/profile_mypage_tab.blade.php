{{--
 * 編集画面tabテンプレート
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - プロフィール</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">プロフィール変更</span></span>
                @else
                    <a href="{{url('/mypage/profile')}}" class="nav-link">プロフィール変更</a></li>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
