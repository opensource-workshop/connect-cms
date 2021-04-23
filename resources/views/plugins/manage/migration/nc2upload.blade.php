{{--
 * NC2 フルバックアップアップロード画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 他システム移行
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">

    {{-- 機能選択タブ --}}
    @include('plugins.manage.migration.migration_manage_tab')

    </div>
    <div class="card-body">

        @if (session('save_favicon'))
        <div class="alert alert-info" role="alert">
            {!!session('save_favicon')!!}
        </div>
        @endif

        <form action="{{url('/')}}/manage/migration/nc2migration" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group row">
                <label for="theme_name" class="col-md-3 text-md-right">NetCommons2<br />フルバックアップ・ファイル</label>
                <div class="col-md-9">
                    <input type="file" name="nc2fullbackup" id="nc2fullbackup" value="{{old('nc2fullbackup')}}">
                    @if ($errors && $errors->has('nc2fullbackup_upload_error')) <div class="text-danger">{{$errors->first('nc2fullbackup_upload_error')}}</div> @endif
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-3 text-md-right">注意 <label class="badge badge-danger">必須</label></div>
                <div class="col-md-9">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="confirm_migration" id="confirm_migration" value="1">
                        <label class="custom-control-label text-nowrap" for="confirm_migration">以下の注意点を理解して実行します。</label>
                    </div>
                    @if ($errors->has('confirm_migration'))
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-circle"></i> データ移行に対する注意点の確認を行ってください。
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-3 text-md-right"></div>
                <div class="col-md-9">
                    <div class="alert alert-warning">
                        NetCommons2 のデータを移行します。<br />
                        現在のサイトのデータはクリアされ、NetCommons2 のデータに置き換わることを理解し、実行してください。
                    </div>
                </div>
            </div>

            {{-- ボタンエリア --}}
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-9">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage')}}'"><i class="fas fa-times"></i> キャンセル</button>
                        <button type="submit" class="btn btn-primary form-horizontal" onclick='javascript:return confirm("NetCommons2インポート処理を実行します。\nサイトのデータはクリアされて、インポートされます。\nよろしいですか？")'>
                            <i class="fas fa-check"></i> アップロード＆移行実施
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
