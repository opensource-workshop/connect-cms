{{--
 * フレーム表示設定編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.photoalbums.photoalbums_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

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

    <form action="{{url('/')}}/redirect/plugin/photoalbums/saveView/{{$page->id}}/{{$frame_id}}/{{$photoalbum->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/editView/{{$page->id}}/{{$frame_id}}/{{$photoalbum->bucket_id}}#frame-{{$frame_id}}">

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
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio"
                           value="0"
                           id="play_view_0"
                           name="play_view"
                           class="custom-control-input"
                           {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::play_view, 0) == '0' ? 'checked' : '' }}
                    >
                    <label class="custom-control-label"
                           for="{{ "play_view_0" }}"
                           id="{{ "label_play_view_0" }}">
                        一覧で再生する
                    </label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio"
                           value="1"
                           id="play_view_1"
                           name="play_view"
                           class="custom-control-input"
                           {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::play_view, 1) == '1' ? 'checked' : '' }}
                    >
                    <label class="custom-control-label"
                           for="{{ "play_view_1" }}"
                           id="{{ "label_play_view_1" }}">
                        一覧はサムネイル画像のみで詳細画面で再生する
                    </label>
                </div>
            </div>
        </div>

        {{-- 詳細画面がある場合の一覧での説明表示文字数 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::description_list_length]}}</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="description_list_length" value="{{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::description_list_length) }}" class="form-control col-sm-3 @if($errors->has('description_list_length')) border-danger @endif">
                @include('plugins.common.errors_inline', ['name' => 'description_list_length'])
                <small class="text-muted">※ 0の場合、全文が表示されます。</small>
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
                        カスタム順の変更は <a href="#manual-sort-preview">現在の並び順プレビュー</a> の上下ボタンから行えます。
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
                        カスタム順の変更は <a href="#manual-sort-preview">現在の並び順プレビュー</a> の上下ボタンから行えます。
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
        @endphp
        <div class="card {{ $is_manual_sort_active ? 'photoalbum-manual-sort__card' : '' }}" id="manual-sort-preview">
            <div class="card-header font-weight-bold d-flex align-items-center justify-content-between">
                <span>
                    <i class="fas fa-list mr-2"></i>現在の並び順プレビュー
                </span>
                {{-- カスタム順が有効な時だけバッジを表示して操作可能であることを明示 --}}
                @if ($is_manual_sort_active)
                    <span class="photoalbum-manual-sort__badge">
                        カスタム順操作可
                    </span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    現在の表示設定での並び順です。利用者画面と同じ順序で表示されます。<br>
                    カスタム順が選択されている対象は、このプレビュー内の上下ボタンで並び替えできます。
                </p>

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
                    ])
                @endif
            </div>
        </div>
    @endif
@endif
@endsection
