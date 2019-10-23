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
    <div class="card-body">
        <ul class="nav nav-tabs">
            {{-- プラグイン側のフレームメニュー --}}
            {{$action_core_frame->includeFrameTab($page, $frame, $action)}}

            {{-- コア側のフレームメニュー --}}
            <li class="nav-item"><a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_setting/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}" class="nav-link">フレーム編集</a></li>
            <li class="nav-item"><a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_delete/{{$page->id}}/{{ $frame->id }}#{{ $frame->id }}" class="nav-link active">フレーム削除</a></li>
        </ul>
    </div>

    {{-- 削除画面(入力フォーム) --}}
    <div class="container-fluid">
        <div class="card border-danger mt-3">
            <div class="card-body">

                <form action="{{url('/core/frame/destroy')}}/{{$page->id}}/{{$frame->frame_id}}" method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <span class="text-danger">
                    フレームを削除します。<br />
                    フレームを元に戻すことはできないため、よく確認して実行してください。<br />
                    ただし、コンテンツのデータそのものは削除されません。<br />
                    </span>
                    <div class="container-fluid">
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                                <i class="fas fa-times"></i> キャンセル
                            </button>
                            <button type="submit" class="btn btn-danger form-horizontal" onclick="javascript:return confirm('フレームを削除します。\nよろしいですか？')">
                                <i class="fas fa-check"></i> フレーム削除
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{{-- </td></tr></table> --}}
