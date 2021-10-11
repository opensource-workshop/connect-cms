{{--
 * PDFアップロード設定
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

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

        @if ($pdf_api_disabled_label)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle"></i> 設定するには、設定ファイルに外部サービス設定が必要です。設定ファイル：<code>.env</code>の <code>PDF_THUMBNAIL_API_URL</code>, <code>PDF_THUMBNAIL_API_KEY</code><br />
            </div>
        @endif

        <form name="form_pdf" method="post" action="{{url('/')}}/manage/service/pdfUpdate">
            {{ csrf_field() }}

            {{-- 初期に選択させるサムネイルの大きさ --}}
            <div class="form-group row">
                <div class="col">

                    @if($pdf_api_disabled_label)
                        <input type="hidden" name="width_of_pdf_thumbnails_initial" value="{{Configs::getConfigsValueAndOld($configs, "width_of_pdf_thumbnails_initial")}}">
                    @endif

                    <label class="col-form-label">初期に選択させるサムネイルの大きさ</label>
                    <select name="width_of_pdf_thumbnails_initial" class="form-control" {{$pdf_api_disabled_label}}>
                        @foreach (WidthOfPdfThumbnail::getMembers() as $enum_value => $enum_label)
                            <div class="custom-control custom-radio custom-control-inline">
                                @if(Configs::getConfigsValueAndOld($configs, "width_of_pdf_thumbnails_initial", WidthOfPdfThumbnail::getDefault()) == $enum_value)
                                    <option value="{{$enum_value}}" selected>{{$enum_label}}</option>
                                @else
                                    <option value="{{$enum_value}}">{{$enum_label}}</option>
                                @endif
                            </div>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 初期に選択させるサムネイルの数 --}}
            <div class="form-group row">
                <div class="col">

                    @if($pdf_api_disabled_label)
                        <input type="hidden" name="number_of_pdf_thumbnails_initial" value="{{Configs::getConfigsValueAndOld($configs, "number_of_pdf_thumbnails_initial")}}">
                    @endif

                    <label class="col-form-label">初期に選択させるサムネイルの数</label>
                    <select name="number_of_pdf_thumbnails_initial" class="form-control" {{$pdf_api_disabled_label}}>
                        @foreach (NumberOfPdfThumbnail::getMembers() as $enum_value => $enum_label)
                            <div class="custom-control custom-radio custom-control-inline">
                                @if(Configs::getConfigsValueAndOld($configs, "number_of_pdf_thumbnails_initial", NumberOfPdfThumbnail::getDefault()) == $enum_value)
                                    <option value="{{$enum_value}}" selected>{{$enum_label}}</option>
                                @else
                                    <option value="{{$enum_value}}">{{$enum_label}}</option>
                                @endif
                            </div>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- サムネイルのリンク --}}
            <div class="form-group">
                <label class="col-form-label">サムネイルのリンク</label>
                <div class="row">
                    <div class="col">

                        @if($pdf_api_disabled_label)
                            <input type="hidden" name="link_of_pdf_thumbnails" value="{{Configs::getConfigsValueAndOld($configs, "link_of_pdf_thumbnails")}}">
                        @endif

                        @foreach (LinkOfPdfThumbnail::getMembers() as $value => $display)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input
                                    type="radio" value="{{$value}}" class="custom-control-input" id="link_of_pdf_thumbnails_{{$value}}" name="link_of_pdf_thumbnails"
                                    @if(Configs::getConfigsValueAndOld($configs, "link_of_pdf_thumbnails", LinkOfPdfThumbnail::getDefault()) == $value) checked @endif
                                    {{$pdf_api_disabled_label}}>
                                <label class="custom-control-label" for="link_of_pdf_thumbnails_{{$value}}">
                                    {{$display}}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-group text-center">
                <button type="reset" class="btn btn-secondary mr-2" {{$pdf_api_disabled_label}}><i class="fas fa-undo-alt"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary form-horizontal" {{$pdf_api_disabled_label}}><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>

    </div><!-- /.card-body -->
</div><!-- /.card -->

@endsection
