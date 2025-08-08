{{--
 * アップロードファイル管理のメインテンプレート
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

        {{-- メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- 使用量表示 --}}
        <div class="row mb-3">
            @if($storage_usage['plan_limit'])
            {{-- プラン容量表示（env設定がある場合のみ） --}}
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-server text-danger"></i> プラン容量
                            <i class="fas fa-question-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="ご契約プランの上限容量です。"></i>
                        </h6>
                        <p class="card-text h5 mb-0 text-danger">{{ $storage_usage['plan_limit'] }}</p>
                        @if($storage_usage['usage_percentage'] !== null)
                        <div class="mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">使用率</span>
                                <span class="small font-weight-bold @if($storage_usage['usage_percentage'] >= 0.9) text-danger @elseif($storage_usage['usage_percentage'] >= 0.7) text-dark @else text-success @endif">
                                    {{ number_format($storage_usage['usage_percentage'] * 100, 1) }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar @if($storage_usage['usage_percentage'] >= 0.9) bg-danger @elseif($storage_usage['usage_percentage'] >= 0.7) bg-warning @else bg-success @endif" 
                                     role="progressbar" 
                                     style="width: {{ min($storage_usage['usage_percentage'] * 100, 100) }}%"
                                     aria-valuenow="{{ $storage_usage['usage_percentage'] * 100 }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <p class="text-muted small mt-1 mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                使用率が高い場合は、不要なファイルを削除してください。
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            @else
            <div class="col-md-6">
            @endif
                <div class="card h-100">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-chart-pie text-primary"></i> 総使用量
                            <i class="fas fa-question-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="データ使用量とアップロードファイル使用量を合計した総容量です。"></i>
                        </h6>
                        <p class="card-text h5 mb-2 text-primary">{{ $storage_usage['total'] }}</p>
                        <div class="row text-sm">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <i class="fas fa-database text-info mr-1"></i>
                                    <span class="text-muted">データ使用量</span>
                                    <i class="fas fa-question-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="データベースに保存されているコンテンツデータの使用量です。"></i>
                                </div>
                                <div class="text-info font-weight-bold">{{ $storage_usage['tables'] }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <i class="fas fa-folder-open text-success mr-1"></i>
                                    <span class="text-muted">アップロードファイル使用量</span>
                                    <i class="fas fa-question-circle text-muted ml-1" data-toggle="tooltip" data-placement="top" title="サーバーにアップロードされたファイルの使用量です。"></i>
                                </div>
                                <div class="text-success font-weight-bold">{{ $storage_usage['uploads'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion" id="search_accordion">
            <div class="card">
                <button class="btn btn-link p-0 text-left collapsed" type="button" data-toggle="collapse" data-target="#search_collapse" aria-expanded="false" aria-controls="search_collapse">
                    <div class="card-header" id="search_condition">
                        絞り込み条件 <i class="fas fa-angle-down"></i>@if (Session::has('search_condition.client_original_name') || Session::has('search_condition.sort'))<span class="badge badge-pill badge-primary ml-2">条件設定中</span>@endif
                   </div>
                </button>
                @if (Session::has('search_condition.client_original_name') || Session::has('search_condition.sort'))
                <div id="search_collapse" class="collapse show" aria-labelledby="search_condition" data-parent="#search_accordion">
                @else
                <div id="search_collapse" class="collapse" aria-labelledby="search_condition" data-parent="#search_accordion">
                @endif
                    <div class="card-body">

                        <form name="form_search" id="form_search" class="form-horizontal" method="post" action="{{url('/')}}/manage/uploadfile/search">
                            {{ csrf_field() }}

                            {{-- ファイル名 --}}
                            <div class="form-group row">
                                <label for="search_condition_client_original_name" class="col-md-3 col-form-label text-md-right">ファイル名</label>
                                <div class="col-md-9">
                                    <input type="text" name="search_condition[client_original_name]" id="search_condition_client_original_name" value="{{Session::get('search_condition.client_original_name')}}" class="form-control">
                                </div>
                            </div>

                            {{-- 並べ替え --}}
                            <div class="form-group row">
                                <label for="sort" class="col-md-3 col-form-label text-md-right">並べ替え</label>
                                <div class="col-md-9">
                                    <select name="search_condition[sort]" id="sort" class="form-control">
                                        <option value="id_asc"@if(Session::get('search_condition.sort') == "id_asc") selected @endif>ID 昇順</option>
                                        <option value="id_desc"@if(Session::get('search_condition.sort') == "id_desc" || !Session::has('search_condition.sort')) selected @endif>ID 降順</option>
                                        <option value="client_original_name_asc"@if(Session::get('search_condition.sort') == "client_original_name_asc") selected @endif>ファイル名 昇順</option>
                                        <option value="client_original_name_desc"@if(Session::get('search_condition.sort') == "client_original_name_desc") selected @endif>ファイル名 降順</option>
                                        <option value="size_asc"@if(Session::get('search_condition.sort') == "size_asc") selected @endif>サイズ 昇順</option>
                                        <option value="size_desc"@if(Session::get('search_condition.sort') == "size_desc") selected @endif>サイズ 降順</option>
                                        <option value="created_at_asc"@if(Session::get('search_condition.sort') == "created_at_asc") selected @endif>アップロード日時 昇順</option>
                                        <option value="created_at_desc"@if(Session::get('search_condition.sort') == "created_at_desc") selected @endif>アップロード日時 降順</option>
                                        <option value="download_count_desc"@if(Session::get('search_condition.sort') == "download_count_desc") selected @endif>ダウンロード数 降順</option>
                                    </select>
                                </div>
                            </div>

                            {{-- ボタンエリア --}}
                            <div class="form-group text-center">
                                <div class="row">
                                    <div class="mx-auto">
                                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/uploadfile/clearSearch')}}'">
                                            <i class="fas fa-times"></i> クリア
                                        </button>
                                        <button type="submit" class="btn btn-primary form-horizontal">
                                            <i class="fas fa-check"></i> 絞り込み
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    {{-- 検索結果情報 --}}
    <div class="row">
        <div class="col-3 text-left d-flex align-items-end">
            {{-- (左側)件数 --}}
            <span class="badge badge-pill badge-light">
                {{ $uploads->total() }} 件
                @if($uploads->total() > 0)
                    {{ '(' . $uploads->firstItem() . '-' . $uploads->lastItem() . ')' }}
                @endif
            </span>
        </div>
        <div class="col text-right d-flex align-items-end justify-content-end">
            {{-- (右側)表示件数選択 --}}
            <form method="post" action="{{url('/')}}/manage/uploadfile/search" class="form-inline">
                {{ csrf_field() }}
                <input type="hidden" name="search_condition[client_original_name]" value="{{Session::get('search_condition.client_original_name')}}">
                <input type="hidden" name="search_condition[sort]" value="{{Session::get('search_condition.sort')}}">
                <label for="per_page_quick" class="mr-2 mb-0">表示件数:</label>
                <select name="uploadfile_per_page" id="per_page_quick" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($allowed_per_page as $per_page_option)
                        <option value="{{ $per_page_option }}"@if(Session::get('uploadfile_per_page') == $per_page_option || (!Session::has('uploadfile_per_page') && $loop->first)) selected @endif>{{ $per_page_option }}件</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <form id="bulk-delete-form" method="post" action="{{url('/')}}/manage/uploadfile/bulkDelete">
        {{ csrf_field() }}
        <div class="table-responsive">
            <table class="table text-nowrap">
            <thead>
                <th nowrap>
                    <input type="checkbox" id="select-all" title="全選択/全解除">
                </th>
                <th nowrap></th>
                <th nowrap>ID</th>
                <th nowrap>ファイル名</th>
                <th nowrap>サイズ</th>
                <th nowrap>アップロード日時</th>
                <th nowrap>プラグイン</th>
                <th nowrap>ダウンロード数</th>
                <th nowrap>ページ</th>
                {{-- <th nowrap>private</th> --}}
                <th nowrap>一時保存フラグ</th>
            </thead>
            <tbody>
                @foreach($uploads as $upload)
                <tr>
                    <td>
                        <input type="checkbox" name="selected_files[]" value="{{$upload->id}}" class="file-checkbox">
                    </td>
                    <td><a href="{{url('/')}}/manage/uploadfile/edit/{{$upload->id}}" id="edit_{{$loop->iteration}}"><i class="far fa-edit"></i></a></td>
                    <td>{{$upload->id}}</td>
                    <td>
                        <a href="{{url('/')}}/file/{{$upload->id}}" target="_blank">
                            {{$upload->client_original_name}}
                            @if ($upload->is_image)
                                {{-- 画像ファイルの場合、サムネイル画像を表示 --}}
                                <img src="{{url('/')}}/file/{{ $upload->id }}" class="w-10" loading="lazy">
                            @endif
                        </a>
                    </td>
                    <td>{{$upload->getFormatSize()}}</td>
                    <td>{{$upload->created_at}}</td>
                    <td>{{$upload->getPluginNameFull()}}</td>
                    <td>{{$upload->download_count}}</td>
                    <td>{!!$upload->getPageLinkTag('_blank')!!}</td>
                    {{-- <td>{{$upload->private}}</td> --}}
                    <td>{{$upload->getTemporaryFlagStr()}}</td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </form>

        {{-- ページング処理 --}}
        @if($uploads)
        <div class="text-center">
            {{$uploads->links()}}
        </div>
        @endif

        {{-- 一括削除ボタン --}}
        <div class="text-center mt-3">
            <button type="button" id="bulk-delete-btn" class="btn btn-danger" disabled>
                <i class="fas fa-trash"></i> 選択したファイルを削除 (<span id="selected-count">0</span>件)
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const fileCheckboxes = document.querySelectorAll('.file-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkDeleteForm = document.getElementById('bulk-delete-form');

        // 全選択/全解除機能
        selectAllCheckbox.addEventListener('change', function() {
            fileCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });

        // 各チェックボックスの変更監視
        fileCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllCheckbox();
                updateBulkDeleteButton();
            });
        });

        // 全選択チェックボックスの状態更新
        function updateSelectAllCheckbox() {
            const checkedBoxes = document.querySelectorAll('.file-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === fileCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < fileCheckboxes.length;
        }

        // 一括削除ボタンの状態更新
        function updateBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.file-checkbox:checked');
            const count = checkedBoxes.length;
            
            selectedCountSpan.textContent = count;
            bulkDeleteBtn.disabled = count === 0;
            
            if (count > 0) {
                bulkDeleteBtn.classList.remove('btn-secondary');
                bulkDeleteBtn.classList.add('btn-danger');
            } else {
                bulkDeleteBtn.classList.remove('btn-danger');
                bulkDeleteBtn.classList.add('btn-secondary');
            }
        }

        // 一括削除ボタンクリック時の確認
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.file-checkbox:checked');
            const count = checkedBoxes.length;
            
            if (count === 0) {
                alert('削除するファイルを選択してください。');
                return;
            }
            
            const fileNames = [];
            checkedBoxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const fileNameLink = row.querySelector('td:nth-child(4) a');
                if (fileNameLink) {
                    fileNames.push(fileNameLink.textContent.trim());
                }
            });
            
            let message = `選択した${count}件のファイルを削除しますか？\n\n削除されるファイル:\n`;
            message += fileNames.slice(0, 10).join('\n');
            if (fileNames.length > 10) {
                message += `\n...他${fileNames.length - 10}件`;
            }
            message += '\n\n※この操作は取り消せません。';
            
            if (confirm(message)) {
                bulkDeleteForm.submit();
            }
        });

        // 初期状態の設定
        updateSelectAllCheckbox();
        updateBulkDeleteButton();

        // ツールチップの初期化
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
