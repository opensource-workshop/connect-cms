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
                @if ($function == "mail")
                    <span class="nav-link"><span class="active">メール設定</span></span>
                @else
                    <a href="{{url('/manage/system/mail')}}" class="nav-link">メール設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "mailTest")
                    <span class="nav-link"><span class="active">メール送信テスト</span></span>
                @else
                    <a href="{{url('/manage/system/mailTest')}}" class="nav-link">メール送信テスト</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "server")
                    <span class="nav-link"><span class="active">サーバ設定</span></span>
                @else
                    <a href="{{url('/manage/system/server')}}" class="nav-link">サーバ設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "log")
                    <span class="nav-link"><span class="active">エラーログ設定</span></span>
                @else
                    <a href="{{url('/manage/system/log')}}" class="nav-link">エラーログ設定</a></li>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
