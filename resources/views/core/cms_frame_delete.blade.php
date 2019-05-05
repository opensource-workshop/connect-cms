{{--
 * CMSフレーム削除画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- フレーム(削除) --}}
{{-- <table class="table"><tr><td> --}}
    <div class="panel-body">
        <ul class="nav nav-tabs">
            {{-- プラグイン側のフレームメニュー --}}
            {{$action_core_frame->includeFrameTab($page, $frame, $action)}}

            {{-- コア側のフレームメニュー --}}
            <li role="presentation"><a href="{{URL::to('/')}}/plugin/blogs/frame_setting/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}">フレーム編集</a></li>
            <li role="presentation" class="active"><a href="{{URL::to('/')}}/plugin/blogs/frame_delete/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}">フレーム削除</a></li>
{{--
            <li role="presentation"><a href="{{URL::to($page->permanent_link)}}/?action=frame_setting&frame_id={{ $frame->frame_id }}#{{ $frame->frame_id }}">フレーム編集</a></li>
            <li role="presentation" class="active"><a href="{{URL::to($page->permanent_link)}}/?action=frame_delete&frame_id={{ $frame->frame_id }}#{{ $frame->frame_id }}">フレーム削除</a></li>
--}}
        </ul>
    </div>

    {{-- 削除画面(入力フォーム) --}}
    <div class="container-fluid">
        <div class="panel panel-danger">
            <div class="panel-body">

                <form action="{{url('/core/frame/destroy')}}/{{$page->id}}/{{$frame->frame_id}}" method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <span class="text-danger">
                    フレームを削除します。<br />
                    フレームを元に戻すことはできないため、よく確認して実行してください。<br />
                    ただし、コンテンツのデータそのものは削除されません。<br />
                    </span>
                    <div class="form-group container-fluid">
                        <div class="pull-right">
                            <button type="button" class="btn btn-default form-horizontal" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
                            <button type="submit" class="btn btn-danger form-horizontal" onclick="javascript:return confirm('フレームを削除します。\nよろしいですか？')">
                                <span class="glyphicon glyphicon-ok"></span> フレーム削除
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{{-- </td></tr></table> --}}
