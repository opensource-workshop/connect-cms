{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category グループ管理
 --}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - グループ管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">グループ一覧</span></span>
                @else
                    <a href="{{url('/manage/group')}}" class="nav-link">グループ一覧</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "edit" && empty($id))
                    <span class="nav-link"><span class="active">グループ登録</span></span>
                @else
                    <a href="{{url('/manage/group/edit')}}" class="nav-link">グループ登録</a></li>
                @endif
                </li>

               <li role="presentation" class="nav-item">
                @if ($function == "edit" && $id)
                    <span class="nav-link"><span class="active">グループ変更</span></span>
                @endif
                </li>

               <li role="presentation" class="nav-item">
                @if ($function == "list")
                    <span class="nav-link"><span class="active">グループ参加者</span></span>
                @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
