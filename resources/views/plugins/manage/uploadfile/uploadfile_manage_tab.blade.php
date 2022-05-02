{{--
 * アップロードファイル管理tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - アップロードファイル管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">アップロードファイル一覧</span></span>
                @else
                    <a href="{{url('/manage/uploadfile')}}" class="nav-link">アップロードファイル一覧</a></li>
                @endif
                </li>
                <li role="presentation" class="nav-item">
                @if ($function == "userdir")
                    <span class="nav-link"><span class="active">ユーザディレクトリ一覧</span></span>
                @else
                    <a href="{{url('/manage/uploadfile/userdir')}}" class="nav-link">ユーザディレクトリ一覧</a></li>
                @endif
                </li>
                <li role="presentation" class="nav-item">
                @if ($function == "edit")
                    <span class="nav-link"><span class="active">アップロードファイル編集</span></span>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
