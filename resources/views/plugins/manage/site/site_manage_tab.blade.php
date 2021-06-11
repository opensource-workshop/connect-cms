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

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();">
                        その他設定
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                        @if ($function == "languages")
                            <a href="{{url('/manage/site/languages')}}" class="dropdown-item active bg-light">多言語設定</a>
                        @else
                            <a href="{{url('/manage/site/languages')}}" class="dropdown-item">多言語設定</a>
                        @endif

                        @if ($function == "pageError")
                            <a href="{{url('/manage/site/pageError')}}" class="dropdown-item active bg-light">エラー設定</a>
                        @else
                            <a href="{{url('/manage/site/pageError')}}" class="dropdown-item">エラー設定</a>
                        @endif

                        @if ($function == "analytics")
                            <a href="{{url('/manage/site/analytics')}}" class="dropdown-item active bg-light">アクセス解析</a>
                        @else
                            <a href="{{url('/manage/site/analytics')}}" class="dropdown-item">アクセス解析</a>
                        @endif

                        @if ($function == "favicon")
                            <a href="{{url('/manage/site/favicon')}}" class="dropdown-item active bg-light">ファビコン</a>
                        @else
                            <a href="{{url('/manage/site/favicon')}}" class="dropdown-item">ファビコン</a>
                        @endif

                        @if ($function == "wysiwyg")
                            <a href="{{url('/manage/site/wysiwyg')}}" class="dropdown-item active bg-light">WYSIWYG設定</a>
                        @else
                            <a href="{{url('/manage/site/wysiwyg')}}" class="dropdown-item">WYSIWYG設定</a>
                        @endif
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</div>
