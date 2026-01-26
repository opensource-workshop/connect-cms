{{--
 * スパム管理のタブ
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - スパム管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">スパムリスト一覧</span></span>
                @else
                    <a href="{{url('/')}}/manage/spam" class="nav-link">スパムリスト一覧</a>
                @endif
                </li>
                @if ($function == "edit")
                <li role="presentation" class="nav-item">
                    <span class="nav-link"><span class="active">スパムリスト編集</span></span>
                </li>
                @endif
            </ul>
        </div>
    </nav>
</div>
