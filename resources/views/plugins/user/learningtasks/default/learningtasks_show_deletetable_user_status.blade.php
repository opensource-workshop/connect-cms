{{--
 * 課題管理記事詳細画面の履歴削除テンプレート。
--}}

{{-- 履歴削除：課題管理者機能 --}}
@if ($tool->canDeletetableUserStatus($user_status_id))
    <div class="text-right">
        <a data-toggle="collapse" href="#collapse-deletetable-user-status{{$user_status_id}}">
            <span class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> 履歴削除</span>
        </a>
    </div>

    <div id="collapse-deletetable-user-status{{$user_status_id}}" class="collapse mt-2">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">一番最後にある履歴データ（評価・提出・申し込み・コメント）を削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/deleteStatus/{{$page->id}}/{{$frame_id}}/{{$user_status_id}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('一番最後の履歴データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
