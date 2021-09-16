{{--
 * WYSIWYG設定のメインテンプレート
--}}

{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg', ['readonly' => 1, 'height' => 150])

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.site.site_manage_tab')
    </div>
    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="alert alert-info" role="alert">
            <i class="fas fa-exclamation-circle"></i> WYSIWYG設定をします。
        </div>

        <div class="form-group">
            <label class="col-form-label">現在のWYSIWYG</label>
            <div class="card border-info">
                <div class="card-body">
                    <textarea></textarea>
                </div>
            </div>
        </div>

        <form action="{{url('/')}}/manage/site/saveWysiwyg" method="POST">
            {{csrf_field()}}

            {{-- 文字サイズ --}}
            <div class="form-group">
                <label class="col-form-label">文字サイズの使用</label>
                <div class="row">
                    <div class="col">

                        @foreach (UseType::getMembers() as $value => $display)
                            <div class="custom-control custom-radio custom-control-inline">
                                @if(Configs::getConfigsValueAndOld($configs, "fontsizeselect") == $value)
                                    <input type="radio" value="{{$value}}" id="fontsizeselect_{{$value}}" name="fontsizeselect" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="{{$value}}" id="fontsizeselect_{{$value}}" name="fontsizeselect" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="fontsizeselect_{{$value}}">{{$display}}</label>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>

    </div>
</div>

@endsection
