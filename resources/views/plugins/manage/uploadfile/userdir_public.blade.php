{{--
 * ユーザパブリックファイル管理の編集テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<script type="text/javascript">
    /** 全選択チェックボックスの押下時 */
    function change_all() {
        // チェックボックスすべてON/OFF
        $('input[type=checkbox][id^=delete_files]').prop("checked", $('#select_all').prop("checked"));
        controlSelectedContentsAndButtons();
    };

    /** 選択リスト（selected-contents）の更新＆ボタンの活性化制御 */
    function controlSelectedContentsAndButtons() {
        // 選択リストを初期化
        $('#selected-contents').html('');

        if ($('input[type="checkbox"][name="delete_files[]"]:checked').length > 0){
            // 選択済みコンテンツが1件以上ある場合：ボタン活性化＆選択リストにコンテンツを詰める
            $('input[type="checkbox"][name="delete_files[]"]:checked').each(function(){
                $('#selected-contents').append('<li>' + $(this).data('name') + '</li>');
            })
            $('.btn-delete').prop('disabled', false);
        } else {
            // 選択済みコンテンツが0件の場合：ボタンdisable化
            $('.btn-delete').prop('disabled', true);
        }
    }

    /** 削除 */
    function deleteContents() {
        if (window.confirm('データを削除します。\nよろしいですか？')) {
            form_user_dir_public.submit();
        }
    }
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.uploadfile.uploadfile_manage_tab')
    </div>

    {{-- 登録後メッセージ表示 --}}
    @include('plugins.common.flash_message')

    {{-- 任意エラーメッセージ表示 --}}
    @if (session('flash_error_message'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {!! session('flash_error_message') !!}
        </div>
    @endif

    <form action="{{url('/manage/uploadfile/deleteUserdirPublic')}}" name="form_user_dir_public" method="POST">
        {{ csrf_field() }}

        <div>
            <span class="badge badge-secondary">public/{{$manage_userdir_public_target}}</span>
        </div>

        <div class="bg-light p-2 text-right">
            <span class="mr-2">チェックした項目を</span>
            <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap">
            <thead>
                <th>
                    {{-- 全選択チェック --}}
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="select_all" onclick="change_all()">
                        <label class="custom-control-label" for="select_all"></label>
                    </div>
                </th>
                <th nowrap>ファイル名</th>
                <th nowrap>サイズ</th>
                <th nowrap>作成日時</th>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr>
                    <td class="align-middle">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="delete_files{{$loop->iteration}}" name="delete_files[]" value="{{$file->getPathname()}}" data-name="{{$file->getRelativePathname()}}" onclick="controlSelectedContentsAndButtons()">
                            <label class="custom-control-label" for="delete_files{{$loop->iteration}}"></label>
                        </div>
                    </td>
                    <td>
                        <a href="{{url('/')}}/{{$manage_userdir_public_target}}/{{$file->getRelativePathname()}}" target="_blank">
                            {{$file->getRelativePathname()}}
                            @if ( FileUtils::isImage(File::mimeType($file->getPathname())) )
                                {{-- 画像ファイルの場合、サムネイル画像を表示 --}}
                                <img src="{{url('/')}}/{{$manage_userdir_public_target}}/{{$file->getRelativePathname()}}" class="w-10" loading="lazy">
                            @endif
                        </a>
                    </td>
                    <td>{{ FileUtils::getFormatSize($file->getSize()) }}</td>
                    <td>{{ new Carbon($file->getCTime()) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">ファイルがありません</td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </form>

    {{-- 削除確認モーダルウィンドウ --}}
    <div class="modal" id="delete-confirm" tabindex="-1" role="dialog" aria-labelledby="delete-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                {{-- ヘッダー --}}
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-title">削除確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{-- メインコンテンツ --}}
                <div class="modal-body">
                    <div class="card border-danger">
                        <div class="card-body">
                            <div class="text-danger">以下のデータを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</div>
                            <ul class="text-danger" id="selected-contents"></ul>
                        </div>
                        <div class="text-center mb-2">
                            {{-- キャンセルボタン --}}
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> キャンセル
                            </button>
                            {{-- 削除ボタン --}}
                            <button type="button" class="btn btn-danger" onclick="deleteContents()"><i class="fas fa-check"></i> 本当に削除する</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
