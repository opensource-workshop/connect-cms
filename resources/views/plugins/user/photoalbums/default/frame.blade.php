{{--
 * フレーム表示設定編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.photoalbums.photoalbums_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<script type="text/javascript">
    $(function () {
        var detailValue = '{{ \App\Enums\PhotoalbumPlayviewType::play_in_detail }}';
        var loadMoreUseValue = '{{ \App\Enums\UseType::use }}';

        function togglePlayViewOptions(value) {
            var isDetail = value == detailValue;
            $('#description_list_length').prop('disabled', !isDetail);
            $('.photoalbum-playview__detail-options').toggleClass('text-muted', !isDetail);
        }

        function toggleLoadMoreOptions() {
            var useValue = $('input[name="load_more_use_flag"]:checked').val();
            var isEnabled = useValue == loadMoreUseValue;
            $('#load_more_count').prop('disabled', !isEnabled);
            $('.photoalbum-load-more-options').toggleClass('text-muted', !isEnabled);
        }

        var isVisibilitySaving = false;
        var pendingVisibilitySave = false;
        var $frameForm = $('#photoalbum-frame-settings-{{ $frame_id }}');
        var $submitButton = $frameForm.find('button[type="submit"]');
        var isSequenceSaving = false;
        var pendingSequenceRequest = null;
        var sequenceEndpoint = '{{ url('/') }}/json/photoalbums/updateViewSequence/{{$page->id}}/{{$frame_id}}';

        $('input[name="load_more_use_flag"]').on('change', toggleLoadMoreOptions);
        toggleLoadMoreOptions();

        function setVisibilitySaving(isSaving) {
            $('.photoalbum-visibility-toggle__input').prop('disabled', isSaving);
            $submitButton.prop('disabled', isSaving);
        }

        function syncHiddenInitialState() {
            $('.photoalbum-visibility-toggle__input').each(function () {
                var isChecked = $(this).is(':checked');
                $(this).data('initial-hidden', isChecked ? 1 : 0);
                $(this).attr('data-initial-hidden', isChecked ? 1 : 0);
            });
        }

        function restoreHiddenFromInitial() {
            $('.photoalbum-visibility-toggle__input').each(function () {
                var initialHidden = $(this).data('initial-hidden') == 1;
                $(this).prop('checked', initialHidden);
            });
        }

        function getItemToggle($item) {
            return $item.children('div').first().find('.photoalbum-visibility-toggle__input');
        }

        function getHiddenState($item, useInitial) {
            var $current = $item;
            while ($current.length) {
                var $toggle = getItemToggle($current);
                if ($toggle.length) {
                    var isHidden = useInitial ? ($toggle.data('initial-hidden') == 1) : $toggle.is(':checked');
                    if (isHidden) {
                        return true;
                    }
                }
                $current = $current.parent().closest('.photoalbum-manual-sort__item');
            }
            return false;
        }

        function refreshHiddenPreview() {
            $('.photoalbum-manual-sort__item').each(function () {
                var $item = $(this);
                var savedHidden = getHiddenState($item, true);
                var currentHidden = getHiddenState($item, false);

                $item.removeClass('photoalbum-manual-sort__item--hidden photoalbum-manual-sort__item--pending-hidden photoalbum-manual-sort__item--pending-show');

                if (savedHidden === currentHidden) {
                    if (currentHidden) {
                        $item.addClass('photoalbum-manual-sort__item--hidden');
                    }
                    return;
                }

                if (currentHidden) {
                    $item.addClass('photoalbum-manual-sort__item--pending-hidden');
                } else {
                    $item.addClass('photoalbum-manual-sort__item--pending-show');
                }
            });
        }

        function collectHiddenFolderIds() {
            return $('.photoalbum-visibility-toggle__input:checked').map(function () {
                return $(this).val();
            }).get();
        }

        function saveHiddenFolders() {
            if (isVisibilitySaving) {
                pendingVisibilitySave = true;
                return;
            }

            isVisibilitySaving = true;
            pendingVisibilitySave = false;
            setVisibilitySaving(true);

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            var hiddenIds = collectHiddenFolderIds();
            hiddenIds.forEach(function (id) {
                formData.append('hidden_folder_ids[]', id);
            });

            axios.post('{{ url('/') }}/json/photoalbums/updateHiddenFolders/{{$page->id}}/{{$frame_id}}', formData)
                .then(function () {
                    syncHiddenInitialState();
                    refreshHiddenPreview();
                })
                .catch(function (error) {
                    restoreHiddenFromInitial();
                    refreshHiddenPreview();
                    var message = '表示設定の更新に失敗しました。';
                    if (error.response && error.response.data && error.response.data.message) {
                        message = error.response.data.message;
                    }
                    alert(message);
                })
                .finally(function () {
                    isVisibilitySaving = false;
                    setVisibilitySaving(false);
                    if (pendingVisibilitySave) {
                        pendingVisibilitySave = false;
                        saveHiddenFolders();
                    }
                });
        }

        function setSequenceSaving(isSaving) {
            $('.photoalbum-sequence-button').prop('disabled', isSaving);
            if (!isSaving) {
                refreshSequenceControls();
            }
        }

        function buildSequenceRequest($form) {
            var contentId = $form.find('input[name="photoalbum_content_id"]').val();
            var operation = $form.find('input[name="display_sequence_operation"]').val();
            if (!contentId || !operation) {
                return null;
            }
            return {
                contentId: contentId,
                operation: operation
            };
        }

        function highlightSequenceItem(contentId) {
            var $item = $('#photoalbum-sort-item-' + contentId);
            if (!$item.length) {
                return;
            }
            $('.photoalbum-manual-sort__item').removeClass('photoalbum-manual-sort__item--active');
            $item.removeClass('photoalbum-manual-sort__item--active');
            void $item[0].offsetWidth;
            $item.addClass('photoalbum-manual-sort__item--active');
        }

        function updateSequenceControls($list) {
            var folders = [];
            var files = [];

            $list.children('.photoalbum-manual-sort__item').each(function () {
                var $item = $(this);
                var isFolder = $item.data('photoalbum-is-folder') == 1;
                if (isFolder) {
                    folders.push($item);
                } else {
                    files.push($item);
                }
            });

            function updateGroup(items) {
                items.forEach(function ($item, index) {
                    var $controls = $item.find('.photoalbum-manual-sort__controls').first();
                    if (!$controls.length) {
                        return;
                    }
                    $controls.find('button[data-sequence-operation="up"]').prop('disabled', index === 0);
                    $controls.find('button[data-sequence-operation="down"]').prop('disabled', index === items.length - 1);
                });
            }

            updateGroup(folders);
            updateGroup(files);
        }

        function refreshSequenceControls() {
            $('.photoalbum-manual-sort__list').each(function () {
                updateSequenceControls($(this));
            });
        }

        function applySequenceUpdate(payload) {
            if (!payload || !payload.siblings || !payload.parent_id) {
                return;
            }

            var $list = $('.photoalbum-manual-sort__list[data-photoalbum-parent-id="' + payload.parent_id + '"]');
            if (!$list.length) {
                return;
            }

            payload.siblings.forEach(function (sibling) {
                var $item = $('#photoalbum-sort-item-' + sibling.id);
                if ($item.length) {
                    $list.append($item);
                }
            });

            updateSequenceControls($list);
            if (payload.moved_id) {
                highlightSequenceItem(payload.moved_id);
            }
            refreshHiddenPreview();
        }

        function requestSequenceUpdate(request) {
            if (isSequenceSaving) {
                pendingSequenceRequest = request;
                return;
            }

            isSequenceSaving = true;
            pendingSequenceRequest = null;
            setSequenceSaving(true);

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('photoalbum_content_id', request.contentId);
            formData.append('display_sequence_operation', request.operation);

            axios.post(sequenceEndpoint, formData)
                .then(function (response) {
                    if (response.data) {
                        applySequenceUpdate(response.data);
                    }
                })
                .catch(function (error) {
                    var message = '並び順の更新に失敗しました。';
                    if (error.response && error.response.data && error.response.data.message) {
                        message = error.response.data.message;
                    }
                    alert(message);
                })
                .finally(function () {
                    isSequenceSaving = false;
                    setSequenceSaving(false);
                    if (pendingSequenceRequest) {
                        var nextRequest = pendingSequenceRequest;
                        pendingSequenceRequest = null;
                        requestSequenceUpdate(nextRequest);
                    }
                });
        }

        $('input[name="play_view"]').on('change', function () {
            togglePlayViewOptions($(this).val());
        });

        refreshHiddenPreview();
        refreshSequenceControls();
        $('.photoalbum-visibility-toggle__input').on('change', function () {
            refreshHiddenPreview();
            saveHiddenFolders();
        });
        $('.photoalbum-sequence-form').on('submit', function (event) {
            event.preventDefault();
            if (isVisibilitySaving || pendingVisibilitySave) {
                alert('表示設定の保存中です。完了してから並び替えてください。');
                return;
            }
            var request = buildSequenceRequest($(this));
            if (!request) {
                return;
            }
            requestSequenceUpdate(request);
        });

        $frameForm.on('submit', function (event) {
            if (isVisibilitySaving || pendingVisibilitySave || isSequenceSaving) {
                event.preventDefault();
                alert('表示設定の保存中です。完了してから変更確定してください。');
            }
        });

        togglePlayViewOptions($('input[name="play_view"]:checked').val());
    });
</script>

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

@if (empty($photoalbum->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用するフォトアルバムを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/photoalbums/saveView/{{$page->id}}/{{$frame_id}}/{{$photoalbum->id}}#frame-{{$frame->id}}" method="POST" class="" id="photoalbum-frame-settings-{{ $frame_id }}">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/editView/{{$page->id}}/{{$frame_id}}/{{$photoalbum->bucket_id}}#frame-{{$frame_id}}">
        <input type="hidden" name="hidden_folder_ids[]" value="">

        {{-- 1ページの表示件数 --}}
        {{-- 現時点では、データ読み込み後にソートしているので、ページングする際は、ソートロジックも見直してから。
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::view_count]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <input type="text" name="view_count" value="{{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::view_count, 10)}}" class="form-control">
            </div>
        </div>
        --}}
        @php
            $current_sort_folder = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_folder);
            $current_sort_file = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_file);
            $play_view_types = \App\Enums\PhotoalbumPlayviewType::getMembers();
            $play_view_default = \App\Enums\PhotoalbumPlayviewType::play_in_list;
            $current_play_view = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::play_view, $play_view_default);
            $description_list_length = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::description_list_length);
            $load_more_use_flag = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::load_more_use_flag, UseType::not_use);
            $load_more_default = (int) config('photoalbums.load_more_image_limit', 10);
            if ($load_more_default < 1) {
                $load_more_default = 10;
            }
            $load_more_max_limit = (int) config('photoalbums.load_more_max_limit', 100);
            if ($load_more_max_limit < 1) {
                $load_more_max_limit = 100;
            }
            $load_more_count = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::load_more_count, $load_more_default);
            $hidden_folder_value = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::hidden_folder_ids, '');
            $hidden_folder_ids = is_array($hidden_folder_value)
                ? $hidden_folder_value
                : explode(FrameConfig::CHECKBOX_SEPARATOR, (string) $hidden_folder_value);
            $hidden_folder_ids = array_values(array_filter(array_map('intval', $hidden_folder_ids), function ($id) {
                return $id > 0;
            }));
            $hidden_folder_map = array_fill_keys($hidden_folder_ids, true);
        @endphp

        {{-- ダウンロード --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::download]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "download_{$key}" }}"
                            name="download"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::download, 0) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "download_{$key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
            <label class="{{$frame->getSettingLabelClass(true)}}"></label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <small class="form-text text-muted mt-0">ゲスト権限でのダウンロード処理の表示を制御します。（編集権限がある場合は表示されます。）</small>
            </div>
        </div>
        {{-- 投稿日 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::posted_at]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "posted_at_{$key}" }}"
                            name="posted_at"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::posted_at, 0) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "posted_at_{$key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- もっと見る --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::load_more_use_flag]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (UseType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "load_more_use_flag_{$key}" }}"
                            name="load_more_use_flag"
                            class="custom-control-input"
                            {{ $load_more_use_flag == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "load_more_use_flag_{$key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
            <label class="{{$frame->getSettingLabelClass(true)}} photoalbum-load-more-options">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::load_more_count]}}</label>
            <div class="{{$frame->getSettingInputClass()}} photoalbum-load-more-options">
                <input type="number" name="load_more_count" id="load_more_count" min="1" max="{{ $load_more_max_limit }}"
                       value="{{ old('load_more_count', $load_more_count) }}" class="form-control">
                <small class="form-text text-muted mt-0">1回あたりの表示件数（最大{{ $load_more_max_limit }}件）</small>
            </div>
        </div>
        {{-- 撮影日 --}}
        {{-- 現時点では、テストなど実施しきれないので、今後へ。
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::shooting_at]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "shooting_at_{$key}" }}"
                            name="shooting_at"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::shooting_at, 0) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "shooting_at_{$key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        --}}
        {{-- 動画の埋め込みコード --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::embed_code]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "embed_code_{$key}" }}"
                            name="embed_code"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::embed_code, 0) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label"
                               for="{{ "embed_code_{$key}" }}"
                               id="{{ "label_embed_code_{$key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- 動画の再生形式 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::play_view]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach ($play_view_types as $play_view_value => $play_view_label)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio"
                               value="{{ $play_view_value }}"
                               id="play_view_{{ $play_view_value }}"
                               name="play_view"
                               class="custom-control-input"
                               {{ $current_play_view == $play_view_value ? 'checked' : '' }}
                        >
                        <label class="custom-control-label"
                               for="play_view_{{ $play_view_value }}"
                               id="label_play_view_{{ $play_view_value }}">
                            {{ $play_view_label }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 詳細画面がある場合の一覧での説明表示文字数 --}}
        <div class="form-group row photoalbum-playview__detail-options {{ $current_play_view == \App\Enums\PhotoalbumPlayviewType::play_in_detail ? '' : 'text-muted' }}">
            <label class="{{$frame->getSettingLabelClass(true)}}" for="description_list_length">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::description_list_length]}}</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text"
                       name="description_list_length"
                       id="description_list_length"
                       value="{{ $description_list_length }}"
                       class="form-control col-sm-3 @if($errors->has('description_list_length')) border-danger @endif"
                       {{ $current_play_view == \App\Enums\PhotoalbumPlayviewType::play_in_detail ? '' : 'disabled' }}
                >
                @include('plugins.common.errors_inline', ['name' => 'description_list_length'])
                <small class="text-info d-block">詳細画面で再生する場合、一覧の説明は指定文字数で切り詰め、長文で一覧が見づらくなるのを抑えます。</small>
                <small class="text-muted">※ 空欄の場合は全文が表示されます。</small>
            </div>
        </div>

        {{-- アルバム並び順 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::sort_folder]}}</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="sort_folder">
                    @foreach (PhotoalbumSort::getMembers() as $sort_key => $sort_view)
                        {{-- 未設定時の初期値 --}}
                        @if ($sort_key == PhotoalbumSort::name_asc && FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_folder) == '')
                            <option value="{{$sort_key}}" selected>{{  $sort_view  }}</option>
                        @else
                            <option value="{{$sort_key}}" @if(FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_folder) == $sort_key) selected @endif>{{  $sort_view  }}</option>
                        @endif
                    @endforeach
                </select>
                @if (($current_sort_folder ?? '') === PhotoalbumSort::manual_order)
                    <small class="form-text text-muted">
                        カスタム順の変更は <a href="#photoalbum-preview">表示プレビュー</a> の上下ボタンから行えます。
                    </small>
                @endif
            </div>
        </div>
        {{-- 写真並び順 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::sort_file]}}</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="sort_file">
                    @foreach (PhotoalbumSort::getMembers() as $sort_key => $sort_view)
                        {{-- 未設定時の初期値 --}}
                        @if ($sort_key == PhotoalbumSort::name_asc && FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_file) == '')
                            <option value="{{$sort_key}}" selected>{{  $sort_view  }}</option>
                        @else
                            <option value="{{$sort_key}}" @if(FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_file) == $sort_key) selected @endif>{{  $sort_view  }}</option>
                        @endif
                    @endforeach
                </select>
                @if (($current_sort_file ?? '') === PhotoalbumSort::manual_order)
                    <small class="form-text text-muted">
                        カスタム順の変更は <a href="#photoalbum-preview">表示プレビュー</a> の上下ボタンから行えます。
                    </small>
                @endif
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="text-center">
            <a class="btn btn-secondary mr-2" href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </a>
            <button type="submit" class="btn btn-primary form-horizontal">
                <i class="fas fa-check"></i>
                <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    変更確定
                </span>
            </button>
        </div>
    </form>

    @if (!empty($photoalbum->id))
        <hr>
        @php
            $manual_sort_redirect = url('/') . '/plugin/photoalbums/editView/' . $page->id . '/' . $frame_id;
            if (!empty($photoalbum->bucket_id)) {
                $manual_sort_redirect .= '/' . $photoalbum->bucket_id;
            }
            $manual_sort_redirect .= '#frame-' . $frame->id;
            $is_manual_sort_folder = ($sort_folder ?? '') === PhotoalbumSort::manual_order;
            $is_manual_sort_file = ($sort_file ?? '') === PhotoalbumSort::manual_order;
            $is_manual_sort_active = $is_manual_sort_folder || $is_manual_sort_file;
            $focus_open_ids = $focus_open_ids ?? [];
        @endphp
        <div class="card photoalbum-preview__card" id="photoalbum-preview">
            <div class="card-header font-weight-bold d-flex align-items-center justify-content-between">
                <span>
                    <i class="fas fa-eye mr-2"></i>表示プレビュー
                </span>
                <div class="d-flex align-items-center">
                    <span class="photoalbum-manual-sort__badge mr-2">
                        表示切替可
                    </span>
                    @if ($is_manual_sort_active)
                        <span class="photoalbum-manual-sort__badge">
                            カスタム順操作可
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="border-bottom pb-2 mb-3">
                    <p class="mb-2">このプレビューでは、フォルダの表示/非表示と並び替えができます。</p>
                    <div class="table-responsive-sm">
                        <table class="table table-sm table-borderless text-muted mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" class="text-nowrap">表示切替</th>
                                    <td><i class="fas fa-eye" aria-hidden="true"></i> 表示 / <i class="fas fa-eye-slash" aria-hidden="true"></i> 非表示</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-nowrap">並び替え</th>
                                    <td>カスタム順のときのみ <i class="fas fa-arrow-up" aria-hidden="true"></i><i class="fas fa-arrow-down" aria-hidden="true"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (empty($manual_sort_root))
                    <p class="text-muted mb-0">表示できるコンテンツがありません。</p>
                @else
                    @include('plugins.user.photoalbums.default.partials.manual_sort_tree', [
                        'node' => $manual_sort_root,
                        'sorted_children_map' => $sorted_children_map,
                        'level' => 0,
                        'page' => $page,
                        'frame_id' => $frame_id,
                        'photoalbum' => $photoalbum,
                        'redirect_path' => $manual_sort_redirect,
                        'sort_folder' => $sort_folder,
                        'sort_file' => $sort_file,
                        'show_controls' => true,
                        'focus_open_ids' => $focus_open_ids,
                        'hidden_folder_map' => $hidden_folder_map,
                        'hidden_parent' => false,
                    ])
                @endif
            </div>
        </div>
    @endif
@endif
@endsection
