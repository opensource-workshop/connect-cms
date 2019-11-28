{{--
 * システム管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category デバックモード
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.system.system_tab')

<form name="form_plugins" id="form_plugins" class="form-horizontal" method="post" action="/manage/system/updateDebugmode">
    {{ csrf_field() }}

    <div class="card">
        <div class="card-body">

            現在のモード：@if($now_debug_mode == '1') デバックモード On @else デバックモード Off @endif <br /><br />

            @if ($now_debug_mode)
                <div class="form-group">
                    <input type="hidden" name="debug_mode" value="0">
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> デバックモードをOff にする。</button>
                </div>
            @else
                <div class="form-group">
                    <input type="hidden" name="debug_mode" value="1">
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> デバックモードをOn にする。</button>
                </div>
            @endif
        </div>
    </div>
</form>

@endsection
