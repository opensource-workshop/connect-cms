{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category システム管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - システム管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">デバックモード</span></span>
                @else
                    <a href="{{url('/manage/system')}}" class="nav-link">デバックモード</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "auth")
                    <span class="nav-link"><span class="active">外部認証</span></span>
                @else
                    <a href="{{url('/manage/system/auth')}}" class="nav-link">外部認証</a></li>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
