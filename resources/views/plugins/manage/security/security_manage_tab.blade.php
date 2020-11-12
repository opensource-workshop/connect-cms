{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category セキュリティ管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - セキュリティ管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">ログイン制限</span></span>
                @else
                    <a href="{{url('/manage/security')}}" class="nav-link">ログイン制限</a></li>
                @endif
                </li>
                <li role="presentation" class="nav-item">
                @if ($function == "purifier")
                    <span class="nav-link"><span class="active">HTML記述制限</span></span>
                @else
                    <a href="{{url('/manage/security/purifier')}}" class="nav-link">HTML記述制限</a></li>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
