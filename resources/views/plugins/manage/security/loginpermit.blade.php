{{--
 * セキュリティ管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category セキュリティ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.security.security_manage_tab')

<div class="card">
<div class="card-body">

    {{-- 削除ボタンのアクション --}}
    <script type="text/javascript">
        function form_delete(id) {
            if (confirm('IP制限設定を削除します。\nよろしいですか？')) {
                form_delete_loginpermit.action = "{{url('/manage/security/deleteLoginPermit')}}/" + id;
                form_delete_loginpermit.submit();
            }
        }
    </script>

    <form action="" method="POST" name="form_delete_loginpermit" class="">
        {{ csrf_field() }}
    </form>

    <form action="/manage/security/saveLoginPermit" method="POST" class="">
        {{ csrf_field() }}

        <div class="card mb-3">
            <div class="card-body">
                基本設定：ログインをどこからでも　　
                <div class="custom-control custom-radio custom-control-inline">
                    @if (empty($configs_login_reject) || $configs_login_reject->value == null || $configs_login_reject->value == '0')
                        <input type="radio" value="0" id="reject_off" name="login_reject" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="reject_off" name="login_reject" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="reject_off">許可する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline pl-3">
                    @if (!empty($configs_login_reject) && $configs_login_reject->value == '1')
                        <input type="radio" value="1" id="reject_on" name="login_reject" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="reject_on" name="login_reject" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="reject_on">拒否する</label>
                </div>
            </div>
        </div>

        {{-- エラーメッセージ --}}
        @if ($errors)
            <div class="card border-danger">
                <div class="card-body">
                    <span class="text-danger">
                        @foreach($errors->all() as $error)
                        <i class="fas fa-exclamation-triangle"></i> {{$error}}<br />
                        @endforeach
                    </span>
                    <span class="text-secondary">
{{--
                        @if ($errors->has('add_apply_sequence') || $errors->has('add_category') || $errors->has('color') || $errors->has('background_color'))
                        <i class="fas fa-exclamation-circle"></i> 追加行を入力する場合は、すべての項目を入力してください。
                        @endif
--}}
                    </span>
                </div>
            </div>
        @endif

        <div class="form-group table-responsive">
            <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th nowrap style="width:10%;">適用順</th>
                    <th nowrap>IPアドレス(*でALL)</th>
                    <th nowrap style="min-width:150px;">権限</th>
                    <th nowrap>許可/拒否</th>
                    <th nowrap><i class="fas fa-trash-alt"></i></th>
                </tr>
            </thead>
            <tbody>
            @foreach($login_permits as $login_permit)
                <tr>
                    <td nowrap>
                        <input type="hidden" value="{{$login_permit->id}}" name="login_permits_id[{{$login_permit->id}}]"></input>
                        <input type="text" value="{{old('apply_sequence.'.$login_permit->id, $login_permit->apply_sequence)}}" name="apply_sequence[{{$login_permit->id}}]" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('ip_address.'.$login_permit->id, $login_permit->ip_address)}}" name="ip_address[{{$login_permit->id}}]" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <select name="role[{{$login_permit->id}}]" class="form-control">
                            <option value="">全権限対象</option>
                            <optgroup label="記事関連の権限">
                            @foreach(Config::get('cc_role.CC_ROLE_LIST') as $cc_role => $cc_role_name)
                                @if ($cc_role == 'admin_system')
                                    </optgroup>
                                    <optgroup label="管理権限">
                                @endif
                            <option value="{{$cc_role}}"@if(old('role.'.$login_permit->id, $cc_role)==$login_permit->role) selected  @endif>{{$cc_role_name}}</option>
                            @endforeach
                            </optgroup>
                        </select>
                    </td>
                    <td nowrap>

                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($login_permit["reject"]) && $login_permit["reject"] == 0)
                            <input type="radio" value="0" id="reject_on[{{$login_permit->id}}]" name="reject[{{$login_permit->id}}]" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="reject_on[{{$login_permit->id}}]" name="reject[{{$login_permit->id}}]" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="reject_on[{{$login_permit->id}}]">許可する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($login_permit["reject"]) && $login_permit["reject"] == 1)
                            <input type="radio" value="1" id="reject_off[{{$login_permit->id}}]" name="reject[{{$login_permit->id}}]" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="reject_off[{{$login_permit->id}}]" name="reject[{{$login_permit->id}}]" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="reject_off[{{$login_permit->id}}]">拒否する</label>
                    </div>

                    </td>
                    <td nowrap>
                        <a href="javascript:form_delete('{{$login_permit->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                    </td>
                </tr>
            @endforeach
            @if ($create_flag)
                <tr>
                    <td nowrap>
                        <input type="text" value="{{old('add_apply_sequence', '')}}" name="add_apply_sequence" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('add_ip_address', '')}}" name="add_ip_address" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <select name="add_role" class="form-control">
                            <option value="">全権限対象</option>
                            <optgroup label="記事関連の権限">
                            @foreach(Config::get('cc_role.CC_ROLE_LIST') as $cc_role => $cc_role_name)
                                @if ($cc_role == 'admin_system')
                                    </optgroup>
                                    <optgroup label="管理権限">
                                @endif
                            <option value="{{$cc_role}}"@if(old('add_role')==$cc_role) selected  @endif>{{$cc_role_name}}</option>
                            @endforeach
                            </optgroup>
                        </select>
                    </td>
                    <td nowrap>
                        <div class="custom-control custom-radio custom-control-inline">
                            @if(old('reject')=="0")
                            <input type="radio" value="0" id="add_reject_on" name="add_reject" class="custom-control-input" checked>
                            @else
                            <input type="radio" value="0" id="add_reject_on" name="add_reject" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="add_reject_on">許可する</label>
                        </div>

                        <div class="custom-control custom-radio custom-control-inline">
                            @if(old('add_reject')=="1")
                            <input type="radio" value="1" id="add_reject_off" name="add_reject" class="custom-control-input" checked>
                            @else
                            <input type="radio" value="1" id="add_reject_off" name="add_reject" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="add_reject_off">拒否する</label>
                        </div>
                    </td>
                    <td nowrap>
                    </td>
                </tr>
            @else
                <tr>
                    <td nowrap>
                        <input type="text" value="{{old('add_apply_sequence', '')}}" name="add_apply_sequence" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('add_ip_address', '')}}" name="add_ip_address" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="add_reject_on" name="add_reject" class="custom-control-input">
                            <label class="custom-control-label" for="add_reject_on">許可する</label>
                        </div>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="add_reject_off" name="add_reject" class="custom-control-input">
                            <label class="custom-control-label" for="add_reject_off">拒否する</label>
                        </div>
                    </td>
                    <td nowrap>
                    </td>
                </tr>
            @endif
            </tbody>
            </table>
        </div>

        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/security')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
        </div>
    </form>

</div>
</div>

@endsection
