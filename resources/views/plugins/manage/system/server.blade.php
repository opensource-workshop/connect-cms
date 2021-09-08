{{--
 * システム管理のサーバ設定テンプレート
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

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <form action="{{url('/')}}/manage/system/updateServer" method="post">
            {{ csrf_field() }}

            {{-- 画像リサイズ時のPHPメモリ数 --}}
            <div class="form-group">
                <label class="col-form-label">画像リサイズ時のPHPメモリ数</label>
                <select name="memory_limit_for_image_resize" class="form-control">
                    @foreach (MemoryLimitForImageResize::getMembers() as $value => $display)
                        <option value="{{$value}}"@if(Configs::getConfigsValueAndOld($configs, "memory_limit_for_image_resize") == $value) selected @endif>{{$display}}</option>
                    @endforeach
                </select>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>

        </form>
    </div>

</div>

@endsection
