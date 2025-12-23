{{--
 * アップロードファイル管理の編集テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.uploadfile.uploadfile_manage_tab')
    </div>
    <div class="card-body">
        <form action="{{url('/manage/uploadfile/save')}}/{{$upload->id}}" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            @if (session('info_message'))
                <div class="alert alert-info">
                    {{session('info_message')}}
                </div>
            @endif

            {{-- ID --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">ID</label>
                <div class="col-md-8 col-form-label">{{$upload->id}}</div>
            </div>

            {{-- ファイル名 --}}
            <div class="form-group row">
                <label for="client_original_name" class="col-md-4 col-form-label text-md-right">ファイル名 <span class="badge badge-danger">必須</span></label>
                <div class="col-md-8">
                    <input type="text" name="client_original_name" id="client_original_name" value="{{old('client_original_name', $upload->getFilename())}}" class="form-control @if($errors->has('client_original_name')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'client_original_name'])
                </div>
            </div>

            {{-- ダウンロード --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">ダウンロード</label>
                <div class="col-md-8 col-form-label"><a href="{{url('/')}}/file/{{$upload->id}}" target="_blank">{{$upload->client_original_name}}</a></div>
            </div>

            {{-- 拡張子 --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">拡張子</label>
                <div class="col-md-8 col-form-label">{{$upload->extension}}</div>
            </div>

            {{-- サイズ --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">サイズ</label>
                <div class="col-md-8 col-form-label">{{$upload->getFormatSize()}}</div>
            </div>

            {{-- アップロード日時 --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">アップロード日時</label>
                <div class="col-md-8 col-form-label">{{$upload->created_at}}</div>
            </div>

            {{-- ダウンロード数 --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">ダウンロード数</label>
                <div class="col-md-8 col-form-label">{{$upload->download_count}}</div>
            </div>

            {{-- 再生回数 --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">
                    再生回数
                    <i class="fas fa-question-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="動画/音声ファイルの再生開始時に回数が増えます。"></i>
                </label>
                <div class="col-md-8 col-form-label">{{$upload->play_count}}</div>
            </div>

            {{-- アップロード・ページ --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">アップロード・ページ</label>
                <div class="col-md-8 col-form-label">{!!$upload->getPageLinkTag('_blank')!!}</div>
            </div>

            {{-- アップロード・プラグイン --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">アップロード・プラグイン</label>
                <div class="col-md-8 col-form-label">{{$upload->getPluginNameFull()}}</div>
            </div>

            {{-- mimetype --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">mimetype</label>
                <div class="col-md-8 col-form-label">{{$upload->mimetype}}</div>
            </div>

            {{-- 一時保存フラグ --}}
            <div class="row">
                <label class="col-md-4 col-form-label text-md-right">一時保存フラグ</label>
                <div class="col-md-8 col-form-label">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="temporary_flag" value="0">
                        <input type="checkbox" name="temporary_flag" value="1" class="custom-control-input" id="temporary_flag" @if(old('temporary_flag', $upload->temporary_flag)) checked=checked @endif>
                        <label class="custom-control-label" for="temporary_flag">一時保存ファイル</label>
                    </div>
                    <div class="alert alert-warning small mb-0">
                        【注意】<br />
                        一時保存ファイルにするとファイルは非公開になります。アップロードした人以外は見えなくなるため、操作する際はご注意ください。<br />
                    </div>
                </div>
            </div>

            {{-- ボタンエリア --}}
            <div class="form-group text-center">
                <div class="row">
                    <div class="col-xl-3"></div>
                    <div class="col-9 col-xl-6 mx-auto">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/uploadfile')}}'">
                            <i class="fas fa-times"></i> キャンセル
                        </button>
                        <button type="submit" class="btn btn-primary form-horizontal">
                            <i class="fas fa-check"></i> 更新
                        </button>
                    </div>
                    <div class="col-3 col-xl-3 text-right">
                        <a data-toggle="collapse" href="#collapse_delete">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-md-inline"> 削除</span></span>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="collapse_delete" class="collapse mt-3">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">アップロードファイルを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/manage/uploadfile/delete')}}/{{$upload->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

@endsection
