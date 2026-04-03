{{--
 * ステータス表示＋ボタン
 *
 * @author 牟田口 満 <akagane99@gmail.com>
 * @category データベース・プラグイン
 *
 * @param $use_button        承認ボタン・編集ボタンを使う
 * @param $add_badge_class   ラベルのクラス追加    
 * @param $input             データベースの1件のデータ
 *
 * // 暗黙で利用
 * @param $frame
 * @param $frame_id
 * @param $buckets
 * @param $page
--}}
@php
    // 承認ボタン・編集ボタンを使うかどうかのフラグ
    $use_button = $use_button ?? 1;
    // ラベルのクラス追加
    $add_badge_class = $add_badge_class ?? '';
@endphp
    
@if ($input->status == 2)
    @can('role_update_or_approval',[[$input, $frame->plugin_name, $buckets]])
        <span class="badge badge-warning {{$add_badge_class}}">承認待ち</span>
    @endcan
    @if ($use_button)
        @can('posts.approval',[[$input, $frame->plugin_name, $buckets]])
            <form action="{{url('/')}}/plugin/databases/approval/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" method="post" name="form_approval" class="d-inline">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                    <i class="fas fa-check"></i> <span class="d-none d-sm-inline">承認</span>
                </button>
            </form>
        @endcan
    @endif
@endif
@can('role_update_or_approval',[[$input, $frame->plugin_name, $buckets]])
    @if (!empty($input->expires_at) && $input->expires_at <= Carbon::now())
        <span class="badge badge-secondary {{$add_badge_class}}">公開終了</span>
    @endif

    @if ($input->posted_at > Carbon::now())
        <span class="badge badge-info {{$add_badge_class}}">公開前</span>
    @endif
@endcan
@can('posts.update',[[$input, $frame->plugin_name, $buckets]])
    @if ($input->status == 1)
        <span class="badge badge-warning {{$add_badge_class}}">一時保存</span>
    @endif

    @if ($use_button)
        <button type="button" class="btn btn-success btn-sm ml-2" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}'">
            <i class="far fa-edit"></i> 編集
        </button>
    @endif
@endcan
