{{--
 * キャビネット画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<div id="app_{{$frame_id}}">
@can('posts.create', [[null, $frame->plugin_name, $buckets]])
<div class="p-2 text-right mb-2">
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}"><i class="fas fa-folder-plus"></i><span class="d-none d-sm-inline"> フォルダ作成</span></button>
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}" id="btn-upload-file"><i class="fas fa-file-upload"></i><span class="d-none d-sm-inline"> ファイル追加</span></button>
</div>
@endcan
<form action="{{url('/')}}/redirect/plugin/cabinets/makeFolder/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("folder_name.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_mkdir{{$frame->id}}">
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="folder_name">フォルダ名</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="folder_name[{{$frame_id}}]" value="{{old("folder_name.$frame_id")}}" class="form-control @if ($errors && $errors->has("folder_name.$frame_id")) border-danger @endif" id="folder_name{{$frame_id}}">
                @if ($errors && $errors->has("folder_name.$frame_id"))
                    <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("folder_name.*")}}</div>
                @endif
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_mkdir{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit" id="button_make_folder{{$frame->id}}">作成</button>
        </div>
    </div>
</form>
<form action="{{url('/')}}/redirect/plugin/cabinets/upload/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}">
    <input type="hidden" name="parent_id" value="{{$parent_id}}">
    <div class="collapse @if ($errors && $errors->has("upload_file.$frame_id")) show @endif bg-light border rounded border-white p-2" aria-expanded="false" aria-controls="collapseOne" id="collapse_upload{{$frame->id}}">
        <div class="form-group">
            <div class="custom-file">
                <input type="hidden" name="upload_file[{{$frame_id}}]" value="">
                <input type="file" name="upload_file[{{$frame_id}}]" value="{{old("upload_file.$frame_id")}}" class="custom-file-input" id="upload_file{{$frame_id}}">
                <label class="custom-file-label @if ($errors && $errors->has("upload_file.$frame_id")) border-danger @endif" for="upload_file" data-browse="参照">ファイル選択...</label>
            </div>
            @if ($errors && $errors->has("upload_file.$frame_id"))
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("upload_file.*")}}</div>
            @endif
        </div>
        <div class="form-group d-none" id="checkbox_zip_deploy">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="zip_deploy" name="zip_deploy" value="true">
                <label class="custom-control-label" for="zip_deploy">ZIPを展開する</label>
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-secondary btn-sm" type="button" data-toggle="collapse" data-target="#collapse_upload{{$frame->id}}">キャンセル</button>
            <button class="btn btn-primary btn-sm" type="submit" id="button_upload_file{{$frame->id}}">追加</button>
            <small id="upload-size-server-help" class="form-text text-muted">アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{UploadMaxSize::getDescription($cabinet->upload_max_size)}}</span></small>
        </div>
    </div>
</form>
<ul class="breadcrumb bg-white">
@foreach($breadcrumbs as $breadcrumb)
    @if (!$loop->last)
        <li class="breadcrumb-item"><a href="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumb->id}}/#frame-{{$frame->id}}">{{$breadcrumb->name}}</a></li>
    @else
        <li class="breadcrumb-item active">{{$breadcrumb->name}}</li>
    @endif
@endforeach
</ul>
<form id="form-cabinet-contents{{$frame_id}}" method="GET">
{{csrf_field()}}
<input type="hidden" name="parent_id" value="{{$parent_id}}">
@include('plugins.common.errors_inline', ['name' => 'cabinet_content_id'])
<div class="bg-light p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame->id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>

@php
    //　表示設定
    $show_download_count = FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::show_download_count, ShowType::not_show) == ShowType::show;
    $show_created_name = FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::show_created_name, ShowType::not_show) == ShowType::show;
    $show_updated_name = FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::show_updated_name, ShowType::not_show) == ShowType::show;
@endphp

<table class="table table-hover text-break">
    <thead>
        <tr class="d-none d-md-table-row">
            <th>
                {{-- 全選択チェック --}}
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="select_all_{{$frame_id}}">
                    <label class="custom-control-label" for="select_all_{{$frame_id}}"></label>
                </div>
            </th>
            <th>名前</th>
            <th>サイズ</th>
            <th>更新日</th>
            @if ($show_created_name)<th>作成者</th>@endif
            @if ($show_updated_name)<th>更新者</th>@endif
            <th></th>
        </tr>
    </thead>
    <tbody>
        {{-- ルート要素の表示時は「1つ上へ」を表示しない --}}
        @if (count($breadcrumbs) > 1)
            <tr>
                <td colspan="@if($show_created_name && $show_updated_name) 7 @elseif($show_created_name || $show_updated_name) 6 @else 5 @endif"><i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$breadcrumbs->last()->parent_id}}/#frame-{{$frame->id}}">1つ上へ</a></td>
            </tr>
        @endif

        @if ($cabinet_contents->count() === 0)
            <tr>
                <td colspan="@if($show_created_name && $show_updated_name) 7 @elseif($show_created_name || $show_updated_name) 6 @else 5 @endif">ファイルがありません</td>
            </tr>
        @else
            @foreach($cabinet_contents as $cabinet_content)
                <tr class="cabinet-item" data-id="{{$cabinet_content->id}}" data-type="@if($cabinet_content->is_folder) folder @else file @endif">
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="customCheck{{$cabinet_content->id}}" name="cabinet_content_id[]" value="{{$cabinet_content->id}}" data-name="{{$cabinet_content->displayName}}">
                            <label class="custom-control-label" for="customCheck{{$cabinet_content->id}}"></label>
                        </div>
                    </td>
                    @if ($cabinet_content->is_folder == true)
                        <td>
                            <i class="fas fa-folder mr-1 text-warning"></i><a href="{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$cabinet_content->id}}/#frame-{{$frame->id}}">{{$cabinet_content->displayName}}</a>
                            <small class="form-text text-muted d-block d-md-none">
                                - | {{$cabinet_content->created_at}}
                            </small>
                            <small class="form-text text-muted d-block d-md-none">
                                @if ($show_created_name)作成者 : {{$cabinet_content->created_name}}@endif
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">-</td>
                        <td class="d-none d-md-table-cell">{{$cabinet_content->created_at}}</td>
                        @if ($show_created_name)<td class="d-none d-md-table-cell">{{$cabinet_content->created_name}}</td>@endif
                        @if ($show_updated_name)<td class="d-none d-md-table-cell">{{$cabinet_content->updated_name}}</td>@endif
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="showContextMenu({{$cabinet_content->id}}, 'folder', '{{addslashes($cabinet_content->name)}}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </td>
                    @else
                        <td>
                            <i class="far fa-file mr-1 text-secondary"></i><a href="{{url('/')}}/file/{{$cabinet_content->upload_id}}" target="_blank">{{$cabinet_content->displayName}}</a>
                            @if ($show_download_count)<span class="badge badge-pill badge-secondary" title="ダウンロード数">{{$cabinet_content->upload->download_count}}</span>@endif
                            <small class="form-text text-muted d-block d-md-none">
                                {{$cabinet_content->upload->getFormatSize()}} | {{$cabinet_content->created_at}}
                            </small>
                            <small class="form-text text-muted d-block d-md-none">
                                @if ($show_created_name)作成者 : {{$cabinet_content->created_name}}@endif
                                @if ($show_updated_name) @if ($show_created_name && $show_updated_name) |  @endif 更新者 : {{$cabinet_content->updated_name}} @endif
                            </small>
                        </td>
                        <td class="d-none d-md-table-cell">{{$cabinet_content->upload->getFormatSize()}}</td>
                        <td class="d-none d-md-table-cell">{{$cabinet_content->updated_at}}</td>
                        @if ($show_created_name)<td class="d-none d-md-table-cell">{{$cabinet_content->created_name}}</td>@endif
                        @if ($show_updated_name)<td class="d-none d-md-table-cell">{{$cabinet_content->updated_name}}</td>@endif
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="showContextMenu({{$cabinet_content->id}}, 'file', '{{addslashes($cabinet_content->name)}}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
<div class="bg-light p-2 text-right">
    <span class="mr-2">チェックした項目を</span>
    @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
    <button class="btn btn-danger btn-sm btn-delete" type="button" data-toggle="modal" data-target="#delete-confirm{{$frame_id}}" disabled><i class="fas fa-trash-alt"></i><span class="d-none d-sm-inline"> 削除</span></button>
    @endcan
    <button class="btn btn-primary btn-sm btn-download" type="button" disabled><i class="fas fa-download"></i><span class="d-none d-sm-inline"> ダウンロード</span></button>
</div>
</form>
@can('posts.delete', [[null, $frame->plugin_name, $buckets]])
{{-- 削除確認モーダルウィンドウ --}}
<div class="modal" id="delete-confirm{{$frame_id}}" tabindex="-1" role="dialog" aria-labelledby="delete-title" aria-hidden="true">
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
                        <ul class="text-danger" id="selected-contents{{$frame_id}}"></ul>
                    </div>
                    <div class="text-center mb-2">
                        {{-- キャンセルボタン --}}
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> キャンセル
                        </button>
                        {{-- 削除ボタン --}}
                        <button type="button" class="btn btn-danger" @click="deleteContents"><i class="fas fa-check"></i> 本当に削除する</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- ケバブメニューモーダル for frame {{$frame_id}} --}}
<div class="modal fade" id="contextModal{{$frame_id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">操作を選択</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    @can('posts.update', [[null, $frame->plugin_name, $buckets]])
                    <button type="button" class="list-group-item list-group-item-action" @click="renameItem">
                        <i class="fas fa-edit mr-2"></i> 名前を変更
                    </button>
                    @endcan
                    <button type="button" class="list-group-item list-group-item-action" @click="downloadSingleItem">
                        <i class="fas fa-download mr-2"></i> ダウンロード
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 名前変更モーダル --}}
@can('posts.update', [[null, $frame->plugin_name, $buckets]])
<div class="modal fade" id="renameModal{{$frame_id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">名前を変更</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- メッセージ表示エリア --}}
                <div id="renameMessage{{$frame_id}}" class="alert" style="display: none; margin-bottom: 1rem;" role="alert">
                    <i class="mr-2"></i>
                    <span class="message-text"></span>
                </div>
                
                <div class="form-group">
                    <label for="newItemName{{$frame_id}}">新しい名前</label>
                    <input type="text" class="form-control" id="newItemName{{$frame_id}}" v-model="newItemName" maxlength="100" @keyup.enter="!isRenameInProgress && confirmRename()" :disabled="isRenameInProgress">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="cancelRename">キャンセル</button>
                <button type="button" class="btn btn-primary" @click="confirmRename" :disabled="isRenameInProgress">
                    <span v-if="isRenameInProgress">
                        <i class="fas fa-spinner fa-spin mr-1"></i>処理中...
                    </span>
                    <span v-else>変更</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endcan
</div> {{-- Vue.js app終了 --}}


<script type="text/javascript">

    const cabinetApp{{$frame_id}} = window.createApp({
        data() {
            return {
                currentItemId: null,
                currentItemType: null,
                currentItemName: null,
                newItemName: '',
                selectedContents: [],
                isAllSelected: false,
                isRenameInProgress: false
            }
        },
        mounted() {
            this.initializeEventListeners();
            this.updateSelectedContents();
        },
        methods: {
            /**
             * モーダル内にメッセージを表示
             * @param {string} message - 表示するメッセージ
             * @param {string} type - メッセージタイプ ('success', 'error', 'warning', 'info')
             */
            showModalMessage(message, type = 'info') {
                const messageElement = document.getElementById('renameMessage{{$frame_id}}');
                if (!messageElement) return;

                // アイコンとクラスの設定
                const iconMap = {
                    'success': 'fas fa-check-circle',
                    'error': 'fas fa-exclamation-triangle',
                    'warning': 'fas fa-exclamation-triangle',
                    'info': 'fas fa-info-circle'
                };

                const classMap = {
                    'success': 'alert-success',
                    'error': 'alert-danger',
                    'warning': 'alert-warning',
                    'info': 'alert-info'
                };

                // メッセージ表示を更新
                const iconClass = iconMap[type] || iconMap['info'];
                const alertClass = classMap[type] || classMap['info'];

                // 既存のクラスをクリアして新しいクラスを追加
                messageElement.className = `alert ${alertClass}`;
                messageElement.querySelector('i').className = iconClass + ' mr-2';
                messageElement.querySelector('.message-text').textContent = message;
                messageElement.style.display = 'block';

                // 成功メッセージの場合、一定時間後に自動で非表示
                if (type === 'success') {
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 3000);
                }
            },

            /**
             * モーダル内のメッセージを非表示
             */
            hideModalMessage() {
                const messageElement = document.getElementById('renameMessage{{$frame_id}}');
                if (messageElement) {
                    messageElement.style.display = 'none';
                }
            },

            /**
             * 名前変更のキャンセル処理
             * 処理状態やメッセージをリセット
             */
            cancelRename() {
                this.isRenameInProgress = false;
                this.hideModalMessage();
                // モーダルが閉じられるので、状態をリセット
            },

            /**
             * イベントリスナーを初期化
             * コラプス制御、ファイル選択、チェックボックス等のイベントを設定
             */
            initializeEventListeners() {
                @can('posts.create', [[null, $frame->plugin_name, $buckets]])
                // フォルダ作成とアップロードのコラプス制御（jQuery版）
                $('#collapse_mkdir{{$frame->id}}').on('hidden.bs.collapse', () => {
                    $('#folder_name{{$frame_id}}').val('');
                });

                $('#collapse_mkdir{{$frame->id}}').on('show.bs.collapse', () => {
                    $('#collapse_upload{{$frame->id}}').collapse('hide');
                });

                $('#collapse_upload{{$frame->id}}').on('show.bs.collapse', () => {
                    $('#collapse_mkdir{{$frame->id}}').collapse('hide');
                });

                $('#collapse_upload{{$frame->id}}').on('hidden.bs.collapse', () => {
                    $('#upload_file{{$frame_id}}').val('');
                    $('#upload_file{{$frame_id}}').next('.custom-file-label').html('ファイル選択...');
                    $('#checkbox_zip_deploy').addClass('d-none');
                    $('#zip_deploy').prop('checked', false);
                });

                // ファイル選択時の処理（jQuery版）
                $('.custom-file-input').on('change', function(){
                    let filename = $(this)[0].files[0].name;
                    let extension = filename.split('.').pop();
                    $(this).next('.custom-file-label').html(filename);
                    if (extension === 'zip') {
                        $('#checkbox_zip_deploy').removeClass('d-none');
                    } else {
                        $('#checkbox_zip_deploy').addClass('d-none');
                        $('#zip_deploy').prop('checked', false);
                    }
                });
                @endcan

                // チェックボックスのイベント
                const checkboxes = document.querySelectorAll('#app_{{$frame_id}} input[type="checkbox"][name="cabinet_content_id[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        this.updateSelectedContents();
                    });
                });

                const selectAllBox = document.getElementById('select_all_{{$frame_id}}');
                if (selectAllBox) {
                    selectAllBox.addEventListener('click', (e) => {
                        this.toggleAllSelection(e.target.checked);
                    });
                }

                const downloadBtn = document.querySelector('#app_{{$frame_id}} .btn-download');
                if (downloadBtn) {
                    downloadBtn.addEventListener('click', () => {
                        this.downloadSelected();
                    });
                }
            },

            /**
             * ケバブメニュー（コンテキストメニュー）を表示
             * @param {number} itemId - アイテムID
             * @param {string} itemType - アイテムタイプ（'file' または 'folder'）
             * @param {string} itemName - アイテム名
             */
            showContextMenu(itemId, itemType, itemName) {
                this.currentItemId = itemId;
                this.currentItemType = itemType;
                this.currentItemName = itemName;
                $('#contextModal{{$frame_id}}').modal('show');
            },

            /**
             * アイテム名前変更モーダルを表示
             * コンテキストメニューを閉じて、名前変更用のモーダルを開く
             */
            renameItem() {
                // 現在のモーダルを閉じる
                $('#contextModal{{$frame_id}}').modal('hide');

                // 状態を初期化
                this.newItemName = this.currentItemName;
                this.isRenameInProgress = false;
                this.hideModalMessage();

                // リネームモーダルを表示
                $('#renameModal{{$frame_id}}').modal('show');

                // モーダルが閉じられた時のイベントリスナーを追加（重複防止のため一度削除）
                $('#renameModal{{$frame_id}}').off('hidden.bs.modal').on('hidden.bs.modal', () => {
                    this.isRenameInProgress = false;
                    this.hideModalMessage();
                });
            },

            /**
             * 名前変更を実行
             * サーバーに名前変更リクエストを送信し、成功時にページをリロード
             */
            async confirmRename() {
                const newName = this.newItemName.trim();

                if (this.isRenameInProgress) {
                    return; // 処理中の場合は何もしない
                }

                if (newName === '') {
                    this.showModalMessage('名前を入力してください。', 'warning');
                    return;
                }

                if (newName === this.currentItemName) {
                    $('#renameModal{{$frame_id}}').modal('hide');
                    return;
                }

                this.isRenameInProgress = true;
                this.hideModalMessage();

                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('cabinet_content_id', this.currentItemId);
                    formData.append('new_name', newName);

                    const response = await axios.post('{{url('/')}}/json/cabinets/rename/{{$page->id}}/{{$frame_id}}', formData);

                    // レスポンスが成功ステータスで、かつサーバー側で処理が成功した場合
                    if (response.status === 200 && response.data && response.data.message) {
                        // 成功メッセージを表示してからリロード
                        this.showModalMessage(response.data.message, 'success');
                        setTimeout(() => {
                            $('#renameModal{{$frame_id}}').modal('hide');
                            window.location.reload();
                        }, 2000);
                    } else {
                        // 予期しないレスポンス形式
                        this.showModalMessage('名前の変更処理で予期しないエラーが発生しました。', 'error');
                        this.isRenameInProgress = false;
                    }
                } catch (error) {
                    let message = '名前の変更に失敗しました。';

                    if (error.response && error.response.data) {
                        if (error.response.data.message) {
                            message = error.response.data.message;
                        } else if (error.response.data.errors) {
                            // バリデーションエラーの場合
                            const errors = error.response.data.errors;
                            const errorMessages = Object.values(errors).flat();
                            message = errorMessages.join('\n');
                        }
                    } else if (error.message) {
                        message = error.message;
                    }

                    this.showModalMessage(message, 'error');
                    this.isRenameInProgress = false;
                }
            },

            /**
             * 選択されたコンテンツの状態を更新
             * チェックボックスの状態に基づいてボタンの有効/無効を制御
             */
            updateSelectedContents() {
                const checkedBoxes = document.querySelectorAll('#app_{{$frame_id}} input[type="checkbox"][name="cabinet_content_id[]"]:checked');
                const allBoxes = document.querySelectorAll('#app_{{$frame_id}} input[type="checkbox"][name="cabinet_content_id[]"]');

                this.selectedContents = Array.from(checkedBoxes).map(box => box.dataset.name);
                this.isAllSelected = checkedBoxes.length === allBoxes.length && allBoxes.length > 0;

                // ボタンの有効/無効制御
                const hasSelection = checkedBoxes.length > 0;
                const downloadBtns = document.querySelectorAll('#app_{{$frame_id}} .btn-download');
                if (downloadBtns) downloadBtns.forEach(dlbtn => dlbtn.disabled = !hasSelection);

                @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
                const deleteBtns = document.querySelectorAll('#app_{{$frame_id}} .btn-delete');
                if (deleteBtns) deleteBtns.forEach(delbtn => delbtn.disabled = !hasSelection);
                @endcan

                // 選択リストの更新
                const selectedList = document.getElementById('selected-contents{{$frame_id}}');
                if (selectedList) {
                    selectedList.innerHTML = this.selectedContents.map(name => `<li>${name}</li>`).join('');
                }

                // 全選択チェックボックスの更新
                const selectAllBox = document.getElementById('select_all_{{$frame_id}}');
                if (selectAllBox) {
                    selectAllBox.checked = this.isAllSelected;
                }
            },

            /**
             * 全選択/全解除を切り替え
             * @param {boolean} checked - 選択状態（true: 全選択, false: 全解除）
             */
            toggleAllSelection(checked) {
                const checkboxes = document.querySelectorAll('#app_{{$frame_id}} input[type=checkbox][id^=customCheck]');
                checkboxes.forEach(box => box.checked = checked);
                this.updateSelectedContents();
            },

            /**
             * 選択されたアイテムを一括ダウンロード
             * フォームのactionを設定してサブミット
             */
            downloadSelected() {
                const form = document.getElementById('form-cabinet-contents{{$frame_id}}');
                form.action = '{{url('/')}}/download/plugin/cabinets/download/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}';
                form.submit();
            },

            /**
             * 単一アイテムをダウンロード
             * 一時的なフォームを作成してダウンロードリクエストを送信
             */
            downloadSingleItem() {
                // モーダルを閉じる
                $('#contextModal{{$frame_id}}').modal('hide');

                // 単一アイテムダウンロード用の一時フォームを作成
                const form = document.createElement('form');
                form.method = 'GET';
                form.action = '{{url('/')}}/download/plugin/cabinets/download/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}';

                // アイテムID
                const itemInput = document.createElement('input');
                itemInput.type = 'hidden';
                itemInput.name = 'cabinet_content_id[]';
                itemInput.value = this.currentItemId;
                form.appendChild(itemInput);

                // 親ID
                const parentInput = document.createElement('input');
                parentInput.type = 'hidden';
                parentInput.name = 'parent_id';
                parentInput.value = '{{$parent_id}}';
                form.appendChild(parentInput);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            },

            @can('posts.delete', [[null, $frame->plugin_name, $buckets]])
            /**
             * 選択されたコンテンツを削除
             * 確認ダイアログを表示後、削除処理を実行
             */
            deleteContents() {
                if (window.confirm('データを削除します。\nよろしいですか？')) {
                    const form = document.getElementById('form-cabinet-contents{{$frame_id}}');
                    form.action = '{{url('/')}}/redirect/plugin/cabinets/deleteContents/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}';
                    form.method = 'POST';

                    const redirectInput = document.createElement('input');
                    redirectInput.type = 'hidden';
                    redirectInput.name = 'redirect_path';
                    redirectInput.value = '{{url('/')}}/plugin/cabinets/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$parent_id}}/#frame-{{$frame->id}}';
                    form.appendChild(redirectInput);

                    form.submit();
                }
            }
            @endcan
        }
    });

    cabinetApp{{$frame_id}}.mount('#app_{{$frame_id}}');
</script>


@endsection
