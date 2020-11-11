{{--
 * セキュリティ管理のHTML記述制限テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category セキュリティ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.security.security_manage_tab')

</div>

<div class="card-body">

    <div class="alert alert-info" role="alert">
        XSS対応のJavaScript等の制限を行います。
    </div>

    <form action="{{url('/manage/security/savePurifier')}}" method="POST" class="form-horizontal">
        {{csrf_field()}}
        <div class="form-group row mb-0">
            <label for="permanent_link" class="col-md-3 col-form-label text-md-right">コンテンツ管理者</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_article_admin'] == 1)
                        <input type="radio" value="1" id="role_article_admin_1" name="role_article_admin" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="role_article_admin_1" name="role_article_admin" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_article_admin_1">制限する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_article_admin'] == 0)
                        <input type="radio" value="0" id="role_article_admin_0" name="role_article_admin" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="role_article_admin_0" name="role_article_admin" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_article_admin_0">制限しない</label>
                </div>
            </div>
        </div>
        <div class="form-group row mb-0">
            <label for="permanent_link" class="col-md-3 col-form-label text-md-right">プラグイン管理者</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_arrangement'] == 1)
                        <input type="radio" value="1" id="role_arrangement_1" name="role_arrangement" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="role_arrangement_1" name="role_arrangement" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_arrangement_1">制限する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_arrangement'] == 0)
                        <input type="radio" value="0" id="role_arrangement_0" name="role_arrangement" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="role_arrangement_0" name="role_arrangement" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_arrangement_0">制限しない</label>
                </div>
            </div>
        </div>
        <div class="form-group row mb-0">
            <label for="permanent_link" class="col-md-3 col-form-label text-md-right">モデレータ</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_article'] == 1)
                        <input type="radio" value="1" id="role_article_1" name="role_article" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="role_article_1" name="role_article" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_article_1">制限する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_article'] == 0)
                        <input type="radio" value="0" id="role_article_0" name="role_article" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="role_article_0" name="role_article" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_article_0">制限しない</label>
                </div>
            </div>
        </div>
        <div class="form-group row mb-0">
            <label for="permanent_link" class="col-md-3 col-form-label text-md-right">承認者</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_approval'] == 1)
                        <input type="radio" value="1" id="role_approval_1" name="role_approval" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="role_approval_1" name="role_approval" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_approval_1">制限する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_approval'] == 0)
                        <input type="radio" value="0" id="role_approval_0" name="role_approval" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="role_approval_0" name="role_approval" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_approval_0">制限しない</label>
                </div>
            </div>
        </div>
        <div class="form-group row mb-0">
            <label for="permanent_link" class="col-md-3 col-form-label text-md-right">編集者</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_reporter'] == 1)
                        <input type="radio" value="1" id="role_reporter_1" name="role_reporter" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="role_reporter_1" name="role_reporter" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_reporter_1">制限する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_reporter'] == 0)
                        <input type="radio" value="0" id="role_reporter_0" name="role_reporter" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="role_reporter_0" name="role_reporter" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_reporter_0">制限しない</label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="permanent_link" class="col-md-3 col-form-label text-md-right">ゲスト</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_guest'] == 1)
                        <input type="radio" value="1" id="role_guest_1" name="role_guest" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="role_guest_1" name="role_guest" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_guest_1">制限する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($purifiers['role_guest'] == 0)
                        <input type="radio" value="0" id="role_guest_0" name="role_guest" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="role_guest_0" name="role_guest" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="role_guest_0">制限しない</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">注意 <label class="badge badge-danger">必須</label></div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="confirm_purifier" value="1">以下のXSSに対する注意点を理解して実行します。
                    </label>
                </div>
                @if ($errors->has('confirm_purifier'))
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle"></i> XSSに対する注意点の確認を行ってください。
                    </div>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right"></div>
            <div class="col-md-9">
                <div class="alert alert-warning">
                    JavaScript等を「制限しない」ことで、XSSの危険性が発生します。<br />
                    「制限しない」に設定する場合は、該当権限のユーザが悪意のあるJavaScriptを埋め込む可能性が発生することを理解し、リスクを許容し実行してください。
                </div>
                <div class="alert alert-primary">
                    設定値の初期値はモデレータ以上のみ「制限しない」です。<br />
                    これは、モデレータ以上の権限はサイトの運営を行っている管理者であるという視点によるものです。
                </div>
            </div>
        </div>

        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/security')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 登録</span></button>
        </div>

    </form>

</div>
</div>

@endsection
