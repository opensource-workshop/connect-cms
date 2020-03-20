{{--
 * システム管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログ設定
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.system.system_tab')

</div>
<div class="card-body">

    <form action="{{url('/')}}/manage/system/updateLog" method="POST">
    {{csrf_field()}}

        {{-- ログ形式 --}}
        <div class="form-group">
            <label class="col-form-label">ログファイルの形式</label>
            <div class="row">
                @if($categories_configs->where('name', 'log_handler')->first()->value == "1")
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="log_handler_0" name="log_handler" class="custom-control-input">
                            <label class="custom-control-label" for="log_handler_0">単一ファイル</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="log_handler_1" name="log_handler" class="custom-control-input" checked="checked">
                            <label class="custom-control-label" for="log_handler_1">日付毎のファイル</label>
                        </div>
                    </div>
                @else
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="log_handler_0" name="log_handler" class="custom-control-input" checked="checked">
                            <label class="custom-control-label" for="log_handler_0">単一ファイル</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="log_handler_1" name="log_handler" class="custom-control-input">
                            <label class="custom-control-label" for="log_handler_1">日付毎のファイル</label>
                        </div>
                    </div>
                @endif
            </div>
            <small class="form-text text-muted">日付毎の場合、ファイル名-YYYY-MM-DD.log の形式で出力されます。</small>
        </div>

        {{-- ログファイル名の指定の有無 --}}
        <div class="form-group">
            <label class="col-form-label">ログファイル名の指定の有無</label>
            <div class="row">
                @if($categories_configs->where('name', 'log_filename_choice')->first()->value == "1")
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="log_filename_choice_0" name="log_filename_choice" class="custom-control-input">
                            <label class="custom-control-label" for="log_filename_choice_0">指定しない</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="log_filename_choice_1" name="log_filename_choice" class="custom-control-input" checked="checked">
                            <label class="custom-control-label" for="log_filename_choice_1">指定する</label>
                        </div>
                    </div>
                @else
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="log_filename_choice_0" name="log_filename_choice" class="custom-control-input" checked="checked">
                            <label class="custom-control-label" for="log_filename_choice_0">指定しない</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="log_filename_choice_1" name="log_filename_choice" class="custom-control-input">
                            <label class="custom-control-label" for="log_filename_choice_1">指定する</label>
                        </div>
                    </div>
                @endif
            </div>
            <small class="form-text text-muted">指定しない場合、Laravel.log、指定する場合、{指定のログファイル名}.log で出力されます。</small>
        </div>

        {{-- ログファイル名 --}}
        <div class="form-group">
            <label class="col-form-label">ログファイル名</label>
            <input type="text" name="log_filename" value="{{$categories_configs->where('name', 'log_filename')->first()->value}}" class="form-control">
            <small class="form-text text-muted">ログファイル名を指定する場合のみ有効</small>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </form>
    <span class="text-danger">※ Blade のエラーがまだ、Catch できていないので、引き続き調査が必要。</span>
</div>
</div>

@endsection
