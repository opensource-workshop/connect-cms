{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 連番管理
 --}}

<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <span class="d-md-none">処理選択</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">

                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">連番設定</span></span>
                @else
                    <a href="{{url('/manage/number')}}" class="nav-link">連番設定</a></li>
                @endif
                </li>

            </ul>
        </div>
    </nav>
</div>
