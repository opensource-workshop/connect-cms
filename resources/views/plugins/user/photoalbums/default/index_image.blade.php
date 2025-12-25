{{--
 * フォトアルバム画面テンプレート（画像・動画）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
{{-- データ一覧に画像が含まれる場合 --}}
@if ($photoalbum_contents->where('is_folder', 0)->isNotEmpty())
@php
if ($frame->isExpandNarrow()) {
    // 右・左エリア = スマホ表示と同等にする
    $col_class = 'col-12';
} else {
    // メインエリア・フッターエリア
    $col_class = 'col-md-4';
}
@endphp
<div class="row">
    @foreach($photoalbum_contents->where('is_folder', 0) as $photoalbum_content)
    <div class="{{$col_class}}">
        <div class="card mt-3 shadow-sm">
        @if ($photoalbum_content->upload->is_image)
            {{-- 画像 --}}
            <img src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}?size=small"
                 id="photo_{{$frame_id}}_{{$loop->iteration}}"
                 style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid" data-toggle="modal" data-target="#image_Modal_{{$frame_id}}_{{$loop->iteration}}"
            >
            <div class="modal fade" id="image_Modal_{{$frame_id}}_{{$loop->iteration}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel_{{$frame_id}}_{{$loop->iteration}}">
                <div class="modal-dialog modal-lg modal-middle">{{-- モーダルウィンドウの縦表示位置を調整・画像を大きく見せる --}}
                    <div class="modal-content pb-3">
                        <div class="modal-body mx-auto">
                            {{-- 拡大表示ウィンドウにも、初期設定でサムネイルを設定しておき、クリック時に実寸画像を読み込みなおす --}}
                            <img src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}?size=small"
                                 style="object-fit: scale-down;"
                                 id="popup_photo_{{$frame_id}}_{{$loop->iteration}}"
                                 class="img-fluid"/>
                        </div>
                        <div class="modal-img_footer">
                            <h5 class="card-title">{{$photoalbum_content->name}}</h5>
                            <p class="card-text">{!!nl2br(e($photoalbum_content->description))!!}</p>
                            <button type="button" class="btn btn-success" data-dismiss="modal">閉じる</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            {{-- サムネイル枠のクリックで、実寸画像を読み込む。一覧表示時のネットワーク通信量の軽減対応 --}}
            $("#photo_{{$frame_id}}_{{$loop->iteration}}").on("click", function() {
               $("#popup_photo_{{$frame_id}}_{{$loop->iteration}}").attr('src', "{{url('/')}}/file/{{$photoalbum_content->upload_id}}");
            });
            </script>
        @elseif ($photoalbum_content->isVideo($photoalbum_content->mimetype) && FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::play_view))
            {{-- 動画：一覧はサムネイル画像のみで詳細画面で再生する --}}
            <a href="{{url('/')}}/plugin/photoalbums/detail/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}">
                <img src="{{url('/')}}/file/{{$photoalbum_content->poster_upload_id}}"
                     style="width: 100%; max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                     id="popup_photo_{{$frame_id}}_{{$loop->iteration}}"
                     class="img-fluid"/>
            </a>
        @elseif ($photoalbum_content->isVideo($photoalbum_content->mimetype))
            {{-- 動画：一覧で再生する --}}
            <video controls controlsList="nodownload"
                 src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}"
                 id="video_{{$loop->iteration}}"
                 style="width: 100%; max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                 class="img-fluid"
                 @if ($photoalbum_content->poster_upload_id) poster="{{url('/')}}/file/{{$photoalbum_content->poster_upload_id}}" @endif
                 oncontextmenu="return false;"
            ></video>
        @endif
            <div class="card-body">
                <div class="d-flex">
                    @if ($download_check)
                        <div class="custom-control custom-checkbox d-inline">
                            <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->name}}">
                            <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
                        </div>
                    @endif
                    @if ($photoalbum_content->name)
                        @if ($photoalbum_content->isVideo($photoalbum_content->mimetype) && FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::play_view))
                            <a href="{{url('/')}}/plugin/photoalbums/detail/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}">
                                <h5 class="card-title d-flex text-break">{{$photoalbum_content->name}}</h5>
                            </a>
                        @else
                            <h5 class="card-title d-flex text-break">{{$photoalbum_content->name}}</h5>
                        @endif
                    @endif
                </div>
                @if ($photoalbum_content->description)
                    <div class="card-text">
                    {{-- 一覧での説明文字数によって切り取って出力する --}}
                    @php $description_list_length = FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::description_list_length); @endphp
                    @if ($photoalbum_content->isVideo($photoalbum_content->mimetype) &&
                         FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::play_view) &&
                         $description_list_length !== '' && $description_list_length < mb_strlen(strip_tags($photoalbum_content->description)))
                        {{ mb_substr(strip_tags($photoalbum_content->description), 0, $description_list_length) }}...
                    @else
                        {!!nl2br(e($photoalbum_content->description))!!}
                    @endif
                    </div>
                @endif
                {{-- 動画を一覧で再生する設定の場合は埋め込みコードを表示する--}}
                @if (($photoalbum_content->isVideo($photoalbum_content->mimetype)) &&
                      FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::embed_code) &&
                      FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::play_view) == 0)
                    <div class="card-text">
                        <a class="embed_code_check" data-name="embed_code{{$photoalbum_content->id}}" style="color: #007bff; cursor: pointer;" id="a_embed_code_check{{$photoalbum_content->id}}"><small>埋め込みコード</small> <i class="fas fa-caret-right"></i></a>
                        <input type="text" name="embed_code[{{$frame_id}}]" value='<iframe width="400" height="300" src="{{url('/')}}/download/plugin/photoalbums/embed/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}" frameborder="0" scrolling="no" allowfullscreen></iframe>' class="form-control" id="embed_code{{$photoalbum_content->id}}" style="display: none;">
                    </div>
                @endif
                @if (FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::posted_at, ShowType::not_show))
                    <div class="card-text"><small>登録日：{{$photoalbum_content->getUpdateOrCreatedAt('Y年n月j日')}}</small></div>
                @endif
                <div class="d-flex justify-content-between align-items-center">
                    @can('posts.update', [[$photoalbum_content, $frame->plugin_name, $buckets]])
                    <a href="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" class="btn btn-sm btn-success">
                        <i class="far fa-edit"></i> 編集
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
