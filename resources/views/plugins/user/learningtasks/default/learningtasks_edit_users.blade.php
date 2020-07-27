{{--
 * 課題管理ユーザ参加登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

{{-- 試験設定フォーム --}}
@if (empty($learningtasks_posts->id))
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">課題データを作成してから、試験の設定をしてください。</p>
        </div>
    </div>
@else
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveUsers/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_users_post">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="/plugin/learningtasks/editUsers/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="form-group row">
        <label class="col-sm-3">参加方式</label>
        <div class="col-sm-9">
{{-- 今後の選択肢として保留（ユーザ選択時のインターフェースの考慮）
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->join_flag == 0)
                    <input type="radio" value="0" id="join_flag_0" name="join_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="join_flag_0" name="join_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="join_flag_0">全員参加</label>
            </div><br />
--}}
{{-- 今後の選択肢として保留（ユーザ選択時のインターフェースの考慮）
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->join_flag == 1)
                    <input type="radio" value="1" id="join_flag_1" name="join_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="join_flag_1" name="join_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="join_flag_1">選択したユーザのみ</label>
            </div><br />
--}}
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->join_flag == 2)
                    <input type="radio" value="2" id="join_flag_2" name="join_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="2" id="join_flag_2" name="join_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="join_flag_2">配置ページのメンバーシップ受講生全員</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->join_flag == 3)
                    <input type="radio" value="3" id="join_flag_3" name="join_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="3" id="join_flag_3" name="join_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="join_flag_3">配置ページのメンバーシップ受講生から選ぶ</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 control-label">メンバーシップ受講生</label>
        <div class="col-sm-9">
            <div class="card p-2">
            {{-- チェックなし用の処理では、削除（参加除外）が必要なため、処理用にhidden で画面のユーザを送る ---}}
            @foreach ($membership_users as $membership_user)
                <input type="hidden" name="page_users[{{$membership_user->id}}]" value="{{$membership_user->id}}">
            @endforeach

            @if ($membership_users->count() == 0)
                ※ 参照権限のあるユーザはいません。
            @else
            @foreach ($membership_users as $membership_user)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="join_users[{{$membership_user->id}}]" value="{{$membership_user->id}}" class="custom-control-input" id="join_users[{{$loop->index}}]" @if($membership_user->id == $membership_user->join_user_id) checked=checked @endif>
                    <label class="custom-control-label" for="join_users[{{$loop->index}}]">
                        {{$membership_user->name}}
                    </label>
                </div>
            @endforeach
            @endif
            </div>
        </div>
    </div>


    <div class="form-group">
        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span> キャンセル</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endif
@endsection
