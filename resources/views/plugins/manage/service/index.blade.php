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
        @include('plugins.manage.service.service_manage_tab')
    </div>

    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i> 外部サービスを利用するWYSIWYG設定です。<br />
            他のWYSIWYG設定は [ サイト管理＞その他設定＞<a href="{{url('/')}}/manage/site/wysiwyg">WYSIWYG設定</a> ] から行えます。
        </div>

        <div class="form-group">
            <label class="col-form-label">現在のWYSIWYG</label>
            <div class="card border-info">
                <div class="card-body">
                    <textarea></textarea>
                </div>
            </div>
        </div>

        <form name="form_service" method="post" action="{{url('/')}}/manage/service/update">
            {{ csrf_field() }}

            {{-- 翻訳 --}}
            <div class="form-group">
                <label class="col-form-label">翻訳</label>
                <div class="row">
                    <div class="col">

                        @if($translate_api_disabled_label)
                            <input type="hidden" name="use_translate" value="{{Configs::getConfigsValueAndOld($configs, "use_translate", UseType::not_use)}}">
                        @endif

                        {{-- ラジオ表示 --}}
                        @foreach (UseType::getMembers() as $value => $display)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input
                                    type="radio" value="{{$value}}" class="custom-control-input" id="use_translate_{{$value}}"
                                    name="use_translate" @if(Configs::getConfigsValueAndOld($configs, "use_translate") == $value) checked @endif
                                    {{$translate_api_disabled_label}}>
                                <label class="custom-control-label" for="use_translate_{{$value}}" id="lavel_use_translate_{{$value}}">
                                    {{$display}}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card bg-light form-text">
                    <div class="card-body px-2 pt-1 pb-1">
                        <span class="small">
                            ※ 外部サービスを利用して、翻訳する機能です。<br />
                            ※ 「使用する」には、設定ファイルに外部サービス設定が必要です。設定ファイル：<code>.env</code>の <code>TRANSLATE_API_URL</code>, <code>TRANSLATE_API_KEY</code><br />
                        </span>
                    </div>
                </div>
            </div>

            {{-- PDFアップロード --}}
            <div class="form-group">
                <label class="col-form-label">PDFアップロード</label>
                <div class="row">
                    <div class="col">

                        @if($pdf_api_disabled_label)
                            <input type="hidden" name="use_pdf_thumbnail" value="{{Configs::getConfigsValueAndOld($configs, "use_pdf_thumbnail", UseType::not_use)}}">
                        @endif

                        {{-- ラジオ表示 --}}
                        @foreach (UseType::getMembers() as $value => $display)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input
                                    type="radio" value="{{$value}}" class="custom-control-input" id="use_pdf_thumbnail_{{$value}}"
                                    name="use_pdf_thumbnail" @if(Configs::getConfigsValueAndOld($configs, "use_pdf_thumbnail") == $value) checked @endif
                                    {{$pdf_api_disabled_label}}>
                                <label class="custom-control-label" for="use_pdf_thumbnail_{{$value}}" id="label_use_pdf_thumbnail_{{$value}}">
                                    {{$display}}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card bg-light form-text">
                    <div class="card-body px-2 pt-1 pb-1">
                        <span class="small">
                            ※ 外部サービスを利用して、PDFアップロード時にサムネイル画像を自動作成する機能です。<br />
                            ※ 「使用する」には、設定ファイルに外部サービス設定が必要です。設定ファイル：<code>.env</code>の <code>PDF_THUMBNAIL_API_URL</code>, <code>PDF_THUMBNAIL_API_KEY</code><br />
                            ※ 詳細設定は <a href="{{url('/')}}/manage/service/pdf">PDFアップロード</a> 画面で設定します。<br>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 顔認識処理 --}}
            <div class="form-group">
                <label class="col-form-label">AI顔認識</label>
                <div class="row">
                    <div class="col">

                        @if($face_ai_api_disabled_label)
                            <input type="hidden" name="use_face_ai" value="{{Configs::getConfigsValueAndOld($configs, "use_face_ai", UseType::not_use)}}">
                        @endif

                        {{-- ラジオ表示 --}}
                        @foreach (UseType::getMembers() as $value => $display)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input
                                    type="radio" value="{{$value}}" class="custom-control-input" id="use_face_ai_{{$value}}"
                                    name="use_face_ai" @if(Configs::getConfigsValueAndOld($configs, "use_face_ai") == $value) checked @endif
                                    {{$face_api_disabled_label}}>
                                <label class="custom-control-label" for="use_face_ai_{{$value}}" id="label_use_face_ai_{{$value}}">
                                    {{$display}}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card bg-light form-text">
                    <div class="card-body px-2 pt-1 pb-1">
                        <span class="small">
                            ※ 外部サービスを利用して、AI顔認識で画像を加工する機能です。<br />
                            ※ 「使用する」には、設定ファイルに外部サービス設定が必要です。設定ファイル：<code>.env</code>の <code>FACE_API_URL</code>, <code>FACE_API_KEY</code><br />
                            ※ 詳細設定は <a href="{{url('/')}}/manage/service/face">AI顔認識</a> 画面で設定します。<br>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 更新ボタン --}}
            <div class="form-group text-center">
                <button type="reset" class="btn btn-secondary mr-2" ><i class="fas fa-undo-alt"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>

    </div><!-- /.card-body -->
</div><!-- /.card -->

@endsection
