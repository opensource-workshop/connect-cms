{{--
 * コンテンツの編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if (!empty($learningtasks_posts->id))
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <span class="d-md-none">編集メニュー</span>
        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg" style="">
            <ul class="navbar-nav">
                @if ($action == 'edit')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">課題設定</span></span>
                    </li>
                @else
                    <li role="presentation" class="nav-item">
                        <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">課題設定</a>
                    </li>
                @endif
                @if ($action == 'editUsers')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">参加設定</span></span>
                    </li>
                @else
                    <li role="presentation" class="nav-item">
                        <a href="{{url('/')}}/plugin/learningtasks/editUsers/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">参加設定</a>
                    </li>
                @endif
                @if ($action == 'editReport')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">レポート設定</span></span>
                    </li>
                @else
                    <li role="presentation" class="nav-item">
                        <a href="{{url('/')}}/plugin/learningtasks/editReport/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">レポート設定</a>
                    </li>
                @endif
                @if ($action == 'editExaminations')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">試験設定</span></span>
                    </li>
                @else
                    <li role="presentation" class="nav-item">
                        <a href="{{url('/')}}/plugin/learningtasks/editExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">試験設定</a>
                    </li>
                @endif
                @if ($action == 'editEvaluate')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">総合評価設定</span></span>
                    </li>
                @else
                    <li role="presentation" class="nav-item">
                        <a href="{{url('/')}}/plugin/learningtasks/editEvaluate/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">総合評価設定</a>
                    </li>
                @endif


                @if ($action == 'listGrade')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">成績出力</span></span>
                    </li>
                @else
                    <li role="presentation" class="nav-item">
                        <a href="{{url('/')}}/plugin/learningtasks/listGrade/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">成績出力</a>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</div>
@endif
