{{--
 * 管理画面tabテンプレート
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - 外部サービス設定</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">WYSIWYG設定</span></span>
                @else
                    <a href="{{url('/')}}/manage/service" class="nav-link">WYSIWYG設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "pdf")
                        <span class="nav-link"><span class="active">PDFアップロード</span></span>
                    @else
                        <a href="{{url('/')}}/manage/service/pdf" class="nav-link">PDFアップロード</a></li>
                    @endif
                </li>

            </ul>
        </div>
    </nav>
</div>
