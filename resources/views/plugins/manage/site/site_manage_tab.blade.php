{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - サイト管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">サイト基本設定</span></span>
                @else
                    <a href="{{url('/manage/site')}}" class="nav-link">サイト基本設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "meta")
                    <span class="nav-link"><span class="active">meta情報</span></span>
                @else
                    <a href="{{url('/manage/site/meta')}}" class="nav-link">meta情報</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "layout")
                    <span class="nav-link"><span class="active">レイアウト設定</span></span>
                @else
                    <a href="{{url('/manage/site/layout')}}" class="nav-link">レイアウト設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "categories")
                    <span class="nav-link"><span class="active">カテゴリ設定</span></span>
                @else
                    <a href="{{url('/manage/site/categories')}}" class="nav-link">カテゴリ設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "languages")
                    <span class="nav-link"><span class="active">多言語設定</span></span>
                @else
                    <a href="{{url('/manage/site/languages')}}" class="nav-link">多言語設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "pageError")
                    <span class="nav-link"><span class="active">エラー設定</span></span>
                @else
                    <a href="{{url('/manage/site/pageError')}}" class="nav-link">エラー設定</a></li>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
