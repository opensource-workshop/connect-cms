{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマ管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - テーマ管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">ユーザ・テーマ</span></span>
                @else
                    <a href="{{url('/manage/theme')}}" class="nav-link">ユーザ・テーマ</a></li>
                @endif
                </li>
                <li role="presentation" class="nav-item">
                @if ($function == "generateIndex")
                    <span class="nav-link"><span class="active">カスタムテーマ生成</span></span>
                @else
                    <a href="{{url('/manage/theme/generateIndex')}}" class="nav-link">カスタムテーマ生成</a></li>
                @endif
                </li>
                @if ($function == "editCss")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">CSS編集</span></span>
                    </li>
                @endif
                @if ($function == "editJs")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">JavaScript編集</span></span>
                    </li>
                @endif
                @if ($function == "listImages")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">画像管理</span></span>
                    </li>
                @endif
                {{-- delete: tinymce7対応. template はTinyMCE 7.xのオープンソース版から削除されてPremium版に移りました
                @if ($function == "editTemplate")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">テンプレート編集</span></span>
                    </li>
                @endif --}}
                @if ($function == "editName")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">テーマ名編集</span></span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</div>
