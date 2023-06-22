{{--
 * サイト管理（サイト設計書）のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.site.site_manage_tab')

</div>
<div class="card-body">

    <script type="text/javascript">
        {{-- ダウンロードのsubmit JavaScript --}}
        function set_additional_parameter_2_download_form() {
            // 追加の出力内容
            document_download.document_secret_name.value = document.querySelector('#document_secret_name').checked ? 1 : null;
            document_download.document_auth_netcomons2_admin_password.value = document.querySelector('#document_auth_netcomons2_admin_password').checked ? 1 : null;
            document_download.document_link_check.value = document.querySelector('#document_link_check').checked ? 1 : null;
        }
        function submit_download() {
            document_download.action = "{{url('/')}}/manage/site/downloadDocument";
            document_download.submit();
        }
        function submit_download_inline() {
            document_download.action = "{{url('/')}}/manage/site/downloadDocument/?disposition=inline";
            document_download.target = "_blank";
            document_download.submit();
        }
        function submit_save_download() {
            save_download.action = "{{url('/')}}/manage/site/saveDocument";
            save_download.submit();
        }
    </script>

    <form action="" method="POST" name="save_download" class="">
        {{ csrf_field() }}

        {{-- 各 大エリアのブラウザ幅 --}}
        追加の出力内容
        <div class="form-group card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label>追加の出力内容</label>
                    </div>
                    <div class="col-md-10">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input name="document_secret_name" value="1" type="checkbox" class="custom-control-input" id="document_secret_name" @if(Configs::getConfigsValueAndOld($configs, "document_secret_name") == "1") checked="checked" @endif>
                            <label class="custom-control-label" for="document_secret_name">API管理の秘密コード</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input name="document_auth_netcomons2_admin_password" value="1" type="checkbox" class="custom-control-input" id="document_auth_netcomons2_admin_password" @if(Configs::getConfigsValueAndOld($configs, "document_auth_netcomons2_admin_password") == "1") checked="checked" @endif>
                            <label class="custom-control-label" for="document_auth_netcomons2_admin_password">外部認証 - NetCommons認証の管理者操作用パスワード</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input name="document_link_check" value="1" type="checkbox" class="custom-control-input" id="document_link_check" @if(Configs::getConfigsValueAndOld($configs, "document_link_check") == "1") checked="checked" @endif>
                            <label class="custom-control-label" for="document_link_check">{{ __('messages.content_url_broken_link_check') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        最終ページの内容
        <div class="form-group card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label class="col-form-label">問い合わせ先タイトル</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" value="{{ Configs::getConfigsValueAndOld($configs, 'document_support_org_title', null) }}" name="document_support_org_title" class="form-control">
                    </div>
                </div>
                <div class="row mt-md-2">
                    <div class="col-md-2">
                        <label class="col-form-label">問い合わせ先の情報等</label>
                    </div>
                    <div class="col-md-10">
                        <textarea name="document_support_org_txt" class="form-control" rows=5>{!! Configs::getConfigsValueAndOld($configs, 'document_support_org_txt', null) !!}</textarea>
                    </div>
                </div>
                <div class="row mt-md-2">
                    <div class="col-md-2">
                        <label class="col-form-label">その他連絡先タイトル</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" value="{{ Configs::getConfigsValueAndOld($configs, 'document_support_contact_title', null) }}" name="document_support_contact_title" class="form-control">
                    </div>
                </div>
                <div class="row mt-md-2">
                    <div class="col-md-2">
                        <label class="col-form-label">その他連絡先の情報等</label>
                    </div>
                    <div class="col-md-10">
                        <textarea name="document_support_contact_txt" class="form-control" rows=5>{!! Configs::getConfigsValueAndOld($configs, 'document_support_contact_txt', null) !!}</textarea>
                    </div>
                </div>
                <div class="row mt-md-2">
                    <div class="col-md-2">
                        <label class="col-form-label">その他記載タイトル</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" value="{{ Configs::getConfigsValueAndOld($configs, 'document_support_other_title', null) }}" name="document_support_other_title" class="form-control">
                    </div>
                </div>
                <div class="row mt-md-2">
                    <div class="col-md-2">
                        <label class="col-form-label">その他記載</label>
                    </div>
                    <div class="col-md-10">
                        <textarea name="document_support_other_txt" class="form-control" rows=5>{!! Configs::getConfigsValueAndOld($configs, 'document_support_other_txt', null) !!}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="" method="POST" name="document_download" class="">
        {{ csrf_field() }}
        <input type="hidden" name="document_secret_name" value="">
        <input type="hidden" name="document_auth_netcomons2_admin_password" value="">
        <input type="hidden" name="document_link_check" value="">
        <div class="form-group text-center">
            <button type="reset" class="btn btn-secondary mr-2"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>

            <button type="button" class="btn btn-primary mr-2" onclick="submit_save_download();"><i class="far fa-save"></i><span class="d-none d-md-inline"> 設定の保存</span></button>

            <div class="btn-group mr-1">
                <button type="button" class="btn btn-primary" onclick="set_additional_parameter_2_download_form(); submit_download();">
                    <i class="fas fa-file-download"></i><span class="d-none d-sm-inline"> ダウンロード</span>
                </button>
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">ドロップダウンボタン</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="set_additional_parameter_2_download_form(); submit_download(); return false;">ダウンロード（ファイル保存）</a>
                    <a class="dropdown-item" href="#" onclick="set_additional_parameter_2_download_form(); submit_download_inline(); return false;">ダウンロード（画面表示）</a>
                </div>
            </div>

        </div>
    </form>
</div>
</div>

@endsection
