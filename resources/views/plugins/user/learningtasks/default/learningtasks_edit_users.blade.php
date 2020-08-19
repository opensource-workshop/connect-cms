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

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <h5><span class="badge badge-secondary">閲覧条件</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">ログインの要否</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if(empty($tool->getFunction('use_need_auth', true)))
                    <input type="radio" value="" id="use_need_auth_null" name="use_need_auth" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_need_auth_null" name="use_need_auth" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_need_auth_null">課題管理設定に従う</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($tool->getFunction('use_need_auth', true) == 'off')
                    <input type="radio" value="off" id="use_need_auth_off" name="use_need_auth" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="off" id="use_need_auth_off" name="use_need_auth" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_need_auth_off">非ログインでも閲覧可能</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($tool->getFunction('use_need_auth', true) == 'on')
                    <input type="radio" value="on" id="use_need_auth_on" name="use_need_auth" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="on" id="use_need_auth_on" name="use_need_auth" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_need_auth_on">閲覧にはログインが必要</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">受講者</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">参加方式</label>
        <div class="col-md-9">
{{-- 今後の選択肢として保留（ユーザ選択時のインターフェースの考慮）
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->student_join_flag == 0)
                    <input type="radio" value="0" id="student_join_flag_0" name="student_join_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="student_join_flag_0" name="student_join_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="student_join_flag_0">全員参加</label>
            </div><br />
--}}
{{-- 今後の選択肢として保留（ユーザ選択時のインターフェースの考慮）
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->student_join_flag == 1)
                    <input type="radio" value="1" id="student_join_flag_1" name="student_join_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="student_join_flag_1" name="student_join_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="student_join_flag_1">選択したユーザのみ</label>
            </div><br />
--}}
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->student_join_flag == 2)
                    <input type="radio" value="2" id="student_join_flag_2" name="student_join_flag" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_membership_user.show">
                @else
                    <input type="radio" value="2" id="student_join_flag_2" name="student_join_flag" class="custom-control-input" data-toggle="collapse" data-target="#collapse_membership_user.show">
                @endif
                <label class="custom-control-label" for="student_join_flag_2">配置ページのメンバーシップ受講者全員</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->student_join_flag == 3)
                    <input type="radio" value="3" id="student_join_flag_3" name="student_join_flag" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_membership_user:not(.show)" aria-expanded="true" aria-controls="collapse_membership_user">
                @else
                    <input type="radio" value="3" id="student_join_flag_3" name="student_join_flag" class="custom-control-input" data-toggle="collapse" data-target="#collapse_membership_user:not(.show)" aria-expanded="true" aria-controls="collapse_membership_user">
                @endif
                <label class="custom-control-label" for="student_join_flag_3">配置ページのメンバーシップ受講者から選ぶ</label>
            </div>
        </div>
    </div>
    @if ($learningtasks_posts->student_join_flag == 3)
    <div class="collapse collapse_membership_user show" id="collapse_membership_user">
    @else
    <div class="collapse collapse_membership_user" id="collapse_membership_user">
    @endif
        <div class="form-group row">
            <label class="col-md-3 text-md-right">受講者</label>
            <div class="col-md-9">
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
    </div>

    <h5><span class="badge badge-secondary">教員</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">参加方式</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->teacher_join_flag == 2)
                    <input type="radio" value="2" id="teacher_join_flag_2" name="teacher_join_flag" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_membership_teacher_user.show">
                @else
                    <input type="radio" value="2" id="teacher_join_flag_2" name="teacher_join_flag" class="custom-control-input" data-toggle="collapse" data-target="#collapse_membership_teacher_user.show">
                @endif
                <label class="custom-control-label" for="teacher_join_flag_2">配置ページのメンバーシップ教員全員</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->teacher_join_flag == 3)
                    <input type="radio" value="3" id="teacher_join_flag_3" name="teacher_join_flag" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_membership_teacher_user:not(.show)" aria-expanded="true" aria-controls="collapse_membership_teacher_user">
                @else
                    <input type="radio" value="3" id="teacher_join_flag_3" name="teacher_join_flag" class="custom-control-input" data-toggle="collapse" data-target="#collapse_membership_teacher_user:not(.show)" aria-expanded="true" aria-controls="collapse_membership_teacher_user">
                @endif
                <label class="custom-control-label" for="teacher_join_flag_3">配置ページのメンバーシップ教員から選ぶ</label>
            </div>
        </div>
    </div>

    @if ($learningtasks_posts->teacher_join_flag == 3)
    <div class="collapse collapse_membership_teacher_user show" id="collapse_membership_teacher_user">
    @else
    <div class="collapse collapse_membership_teacher_user" id="collapse_membership_teacher_user">
    @endif
        <div class="form-group row">
            <label class="col-md-3 text-md-right">教員</label>
            <div class="col-md-9">
                <div class="card p-2">
                {{-- チェックなし用の処理では、削除（参加除外）が必要なため、処理用にhidden で画面のユーザを送る ---}}
                @foreach ($membership_teacher_users as $membership_teacher_user)
                    <input type="hidden" name="page_teacher_users[{{$membership_teacher_user->id}}]" value="{{$membership_teacher_user->id}}">
                @endforeach

                @if ($membership_teacher_users->count() == 0)
                    ※ 参照権限のあるユーザはいません。
                @else
                @foreach ($membership_teacher_users as $membership_teacher_user)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="join_teacher_users[{{$membership_teacher_user->id}}]" value="{{$membership_teacher_user->id}}" class="custom-control-input" id="join_teacher_users[{{$loop->index}}]" @if($membership_teacher_user->id == $membership_teacher_user->join_user_id) checked=checked @endif>
                        <label class="custom-control-label" for="join_teacher_users[{{$loop->index}}]">
                            {{$membership_teacher_user->name}}
                        </label>
                    </div>
                @endforeach
                @endif
                </div>
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
