{{--
 * tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 祝日管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - 祝日管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">祝日一覧</span></span>
                @else
                    <a href="{{url('/manage/holiday')}}" class="nav-link">祝日一覧</a></li>
                @endif
                </li>
                <li role="presentation" class="nav-item">
                @if ($function == "edit")
                    <span class="nav-link"><span class="active">祝日登録</span></span>
                @else
                    <a href="{{url('/manage/holiday/edit')}}" class="nav-link">祝日登録</a></li>
                @endif
                <li role="presentation" class="nav-item">
                @if ($function == "overrideEdit")
                    <span class="nav-link"><span class="active">祝日上書き</span></span>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
