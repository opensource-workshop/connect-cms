{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@php
    // テーマ固有書式
    $style_formats_file = '';
    $style_formats_path = public_path() . '/themes/' . $theme . '/wysiwyg/style_formats.txt';
    $style_formats_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/style_formats.txt';
    $style_formats_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/style_formats.txt';
    if (File::exists($style_formats_path)) {
        $style_formats_file = File::get($style_formats_path);
    }
    else if (File::exists($style_formats_group_default_path)) {
        $style_formats_file = File::get($style_formats_group_default_path);
    }
    else if (File::exists($style_formats_default_path)) {
        $style_formats_file = File::get($style_formats_default_path);
    }

    // テーマ固有スタイル
    $block_formats_file = '';
    $block_formats_path = public_path() . '/themes/' . $theme . '/wysiwyg/block_formats.txt';
    $block_formats_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/block_formats.txt';
    $block_formats_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/block_formats.txt';
    if (File::exists($block_formats_path)) {
        $block_formats_file = File::get($block_formats_path);
    }
    else if (File::exists($block_formats_group_default_path)) {
        $block_formats_file = File::get($block_formats_group_default_path);
    }
    else if (File::exists($block_formats_default_path)) {
        $block_formats_file = File::get($block_formats_default_path);
    }

    // CSS
    $content_css_file = '';
    $content_css_path = public_path() . '/themes/' . $theme . '/wysiwyg/content_css.txt';
    $content_css_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/content_css.txt';
    $content_css_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/content_css.txt';
    if (File::exists($content_css_path)) {
        $content_css_file = File::get($content_css_path);
    }
    else if (File::exists($content_css_group_default_path)) {
        $content_css_file = File::get($content_css_group_default_path);
    }
    else if (File::exists($content_css_default_path)) {
        $content_css_file = File::get($content_css_default_path);
    }

    // テーブル
    $table_class_list_file = '';
    $table_class_list_path = public_path() . '/themes/' . $theme . '/wysiwyg/table_class_list.txt';
    $table_class_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/table_class_list.txt';
    $table_class_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/table_class_list.txt';
    if (File::exists($table_class_list_path)) {
        $table_class_list_file = File::get($table_class_list_path);
    }
    else if (File::exists($table_class_group_default_path)) {
        $table_class_list_file = File::get($table_class_group_default_path);
    }
    else if (File::exists($table_class_default_path)) {
        $table_class_list_file = File::get($table_class_default_path);
    }

    // テーブルセル
    $table_cell_class_list_file = '';
    $table_cell_class_list_path = public_path() . '/themes/' . $theme . '/wysiwyg/table_cell_class_list.txt';
    $table_cell_class_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/table_cell_class_list.txt';
    $table_cell_class_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/table_cell_class_list.txt';
    if (File::exists($table_cell_class_list_path)) {
        $table_cell_class_list_file = File::get($table_cell_class_list_path);
    }
    else if (File::exists($table_cell_class_group_default_path)) {
        $table_cell_class_list_file = File::get($table_cell_class_group_default_path);
    }
    else if (File::exists($table_cell_class_default_path)) {
        $table_cell_class_list_file = File::get($table_cell_class_default_path);
    }

    // テーマ固有 箇条書きリスト（ULタグ）の表示設定
    $advlist_bullet_lists_file = '';
    $advlist_bullet_lists_path = public_path() . '/themes/' . $theme . '/wysiwyg/advlist_bullet_lists.txt';
    $advlist_bullet_lists_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/advlist_bullet_lists.txt';
    $advlist_bullet_lists_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/advlist_bullet_lists.txt';
    if (File::exists($advlist_bullet_lists_path)) {
        $advlist_bullet_lists_file = File::get($advlist_bullet_lists_path);
    }
    else if (File::exists($advlist_bullet_lists_group_default_path)) {
        $advlist_bullet_lists_file = File::get($advlist_bullet_lists_group_default_path);
    }
    else if (File::exists($advlist_bullet_lists_default_path)) {
        $advlist_bullet_lists_file = File::get($advlist_bullet_lists_default_path);
    }

    // テーマ固有 番号箇条書きリスト（OLタグ）の表示設定
    $advlist_number_lists_file = '';
    $advlist_number_lists_path = public_path() . '/themes/' . $theme . '/wysiwyg/advlist_number_lists.txt';
    $advlist_number_lists_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/advlist_number_lists.txt';
    $advlist_number_lists_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/advlist_number_lists.txt';
    if (File::exists($advlist_number_lists_path)) {
        $advlist_number_lists_file = File::get($advlist_number_lists_path);
    }
    else if (File::exists($advlist_number_lists_group_default_path)) {
        $advlist_number_lists_file = File::get($advlist_number_lists_group_default_path);
    }
    else if (File::exists($advlist_number_lists_default_path)) {
        $advlist_number_lists_file = File::get($advlist_number_lists_default_path);
    }

    // テーマ固有 簡易テンプレート
    $templates_file = '';
    $templates_path = public_path() . '/themes/' . $theme . '/wysiwyg/templates.txt';
    $templates_group_default_path = public_path() . '/themes/' . $theme_group_default . '/wysiwyg/templates.txt';
    $templates_default_path = public_path() . '/themes/Defaults/Default/wysiwyg/templates.txt';
    if (File::exists($templates_path)) {
        $templates_file = File::get($templates_path);
    }
    else if (File::exists($templates_group_default_path)) {
        $templates_file = File::get($templates_group_default_path);
    }
    else if (File::exists($templates_default_path)) {
        $templates_file = File::get($templates_default_path);
    }

    // TinyMCE Body クラス
    $body_class = '';
    if ($frame->area_id == 0) {
        $body_class = 'ccHeaderArea';
    }
    elseif ($frame->area_id == 1) {
        $body_class = 'ccCenterArea ccLeftArea';
    }
    elseif ($frame->area_id == 2) {
        $body_class = 'ccCenterArea ccMainArea';
    }
    elseif ($frame->area_id == 3) {
        $body_class = 'ccCenterArea ccRightArea';
    }
    elseif ($frame->area_id == 4) {
        $body_class = 'ccFooterArea';
    }

    // plugins
    // change: tinymce5対応. textcolorは coreに含まれたため除外
    // $plugins = 'file image imagetools media link autolink preview textcolor code table lists advlist template ';
    $plugins = 'file image imagetools media link autolink preview code table lists advlist template hr ';
    if (Configs::getConfigsValue($cc_configs, 'use_translate', UseType::not_use) == UseType::use) {
        $plugins .= ' translate';
    }
    if (Configs::getConfigsValue($cc_configs, 'use_pdf_thumbnail')) {
        $plugins .= ' pdf';
    }

    // モザイク
    $plugins .= ' face';

    $plugins = "plugins  : '" . $plugins . "',";

    // 文字サイズの選択
    $toolbar_fontsizeselect = '';
    if (Configs::getConfigsValue($cc_configs, 'fontsizeselect')) {
        $toolbar_fontsizeselect = '| fontsizeselect';
    }

    // toolbar
    // change: tinymce5対応
    // $toolbar = 'undo redo | bold italic underline strikethrough subscript superscript | formatselect | styleselect | forecolor backcolor | removeformat | table | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | link jbimages | image file media | preview | code ';
    $toolbar = "undo redo | bold italic underline strikethrough subscript superscript {$toolbar_fontsizeselect} | styleselect | forecolor backcolor | removeformat | table hr | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | link | image file media | preview | code";
    $mobile_toolbar = "undo redo | image file media | link | code | bold italic underline strikethrough subscript superscript {$toolbar_fontsizeselect} | styleselect | forecolor backcolor | removeformat | table hr | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | preview";
    // 簡易テンプレート設定がない場合、テンプレート挿入ボタン押下でエラー出るため、設定ない場合はボタン表示しない。
    if (! empty($templates_file)) {
        $toolbar .= '| template ';
        $mobile_toolbar .= '| template ';
    }
    // いずれかの外部サービスONの場合、頭に区切り文字 | を追加する
    if (Configs::getConfigsValue($cc_configs, 'use_translate', UseType::not_use) == UseType::use || Configs::getConfigsValue($cc_configs, 'use_pdf_thumbnail')) {
        $toolbar .= ' | ';
        $mobile_toolbar .= ' | ';
    }
    if (Configs::getConfigsValue($cc_configs, 'use_translate', UseType::not_use) == UseType::use) {
        $toolbar .= ' translate ';
        $mobile_toolbar .= ' translate ';
    }
    if (Configs::getConfigsValue($cc_configs, 'use_pdf_thumbnail')) {
        $toolbar .= ' pdf ';
        $mobile_toolbar .= 'pdf ';
    }

    // モザイク
    $toolbar .= ' face ';

    $toolbar = "toolbar  : '" . $toolbar . "',";
    $mobile_toolbar = "toolbar  : '" . $mobile_toolbar . "',";

    $pc_toolbar_mode = '';
    if (!is_null($frame) && $frame->isExpandNarrow()) {
        // 左右エリアは、スマホ表示と同等にする
        $pc_toolbar_mode = 'floating';
        $toolbar = $mobile_toolbar;
    } else {
        $pc_toolbar_mode = 'wrap';
    }

    // imagetools_toolbar (need imagetools plugin)
    // rotateleft rotateright flipv fliphは、フォーカスが外れないと images_upload_handler が走らないため、使わない。フォーカスが外さないで確定すると、固定記事の場合、コンテンツカラム内にbase64画像（超長い文字列)がそのまま送られ、カラムサイズオーバーでSQLエラーになる。
    // しかし editimage (画像の編集) であれば、モーダルを開いてそこで編集し「保存」ボタンを押下時に images_upload_handler が走るため、base64問題を回避できる。
    $imagetools_toolbar = "imagetools_toolbar  : 'editimage imageoptions',";

@endphp

{{-- 非表示のinput type file. file plugin用. see) public\js\tinymce\plugins\file\plugin.min.js --}}
<input type="file" class="d-none" id="cc-file-upload-file1-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file2-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file3-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file4-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file5-{{$frame_id}}">

{{-- 非表示のinput type file. pdf plugin用. see) public\js\tinymce\plugins\pdf\plugin.min.js --}}
<input type="file" class="d-none" id="cc-pdf-upload-{{$frame_id}}">

{{-- 非表示のinput type file. face plugin用. see) public\js\tinymce\plugins\face\plugin.min.js --}}
<input type="file" class="d-none" id="cc-face-upload-{{$frame_id}}">

{{-- bugfix: iphone or ipad + safari のみ、DOM(実際のinput type file)がないと機能しないため対応
    see) https://stackoverflow.com/questions/47664777/javascript-file-input-onchange-not-working-ios-safari-only --}}
<input type="file" class="d-none" id="cc-file-upload-file-{{$frame_id}}">

{{-- 登録時のリサイズ用 --}}
<input type="text" class="d-none" id="cc-resized-image-size-{{$frame_id}}">

{{-- tinymce5対応. 同フォルダでライブラリを入れ替えたため、ファイル名の後ろに?付けてブラウザキャッシュ対応 --}}
<script type="text/javascript" src="{{url('/')}}/js/tinymce/tinymce.min.js?v=5.8.0"></script>
<script type="text/javascript">
    tinymce.init({
        @if(isset($target_class) && $target_class)
            selector : 'textarea.{{$target_class}}',
        @else
            selector : 'textarea',
        @endif

        cache_suffix: '?v=5.8.0.11',

        // change: app.blade.phpと同様にlocaleを見て切替
        // language : 'ja',
        language : '{{ app()->getLocale() }}',

        // change: tinymce5対応
        // base_url : '{{url("/")}}',
        document_base_url : '{{url("/")}}',

        @if(isset($readonly) && $readonly)
            readonly : 1,
        @endif

        {{-- plugins --}}
        {!!$plugins!!}

        {{-- imagetools_toolbar --}}
        {!!$imagetools_toolbar!!}

        {{-- formatselect = スタイル, styleselect = 書式 --}}
        {!!$toolbar!!}

        // fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
        fontsize_formats: '0.65rem 0.85rem 1rem 1.15rem 1.3rem 1.5rem 2rem 3rem',

        {{-- テーマ固有書式 --}}
        {!!$style_formats_file!!}

        {{-- テーマ固有スタイル --}}
        {!!$block_formats_file!!}

        {{-- テーマ固有 箇条書きリスト（ULタグ）の表示設定 --}}
        {!!$advlist_bullet_lists_file!!}

        {{-- テーマ固有 番号箇条書きリスト（OLタグ）の表示設定 --}}
        {!!$advlist_number_lists_file!!}

        {{-- テーマ固有 簡易テンプレート設定 --}}
        {!!$templates_file!!}

        formats: {
            // bugfix: bootstrap4のblockquoteはclassに'blockquote'付ける
            blockquote: { block: 'blockquote', classes: 'blockquote' }
        },

        menubar  : '',
        contextmenu : '',

        // add: tinymce5対応
        // toolbar_mode : 'wrap',
        toolbar_mode : '{{$pc_toolbar_mode}}',
        mobile: {
            toolbar_mode : 'floating',

            {{-- formatselect = スタイル, styleselect = 書式 --}}
            {!!$mobile_toolbar!!}
        },

        relative_urls : false,
        height: {{ isset($height) ? $height : 300 }},
        branding: false,
        //forced_root_block : false,
        valid_children : "+body[style|input],+a[div|p],",
        //extended_valid_elements : "script[type|charset|async|src]"
        //                         +",div[id|class|align|style|clear]"
        //                         +",input[*]"
        //                         +",cc[*]",
        //extended_valid_elements : "script[type|charset|async|src],cc[value]",
        valid_elements : '*[*]',
        extended_valid_elements : '*[*]',

        {{-- CSS --}}
        {!!$content_css_file!!}

        body_class : "{{$body_class}}",

        file_picker_types: 'file image media',

        // add: tinymce5対応
        file_picker_callback: function(callback, value, meta) {
            // console.log(meta.filetype, meta.fieldname);

            /* Provide file and text for the link dialog
            --------------------------------------------- */
            if (meta.filetype == 'file') {
                // callback('mypage.html', {text: 'My text'});

                // file plugin. フィールド名の先頭がfileであれば. file1～5
                if (meta.fieldname.startsWith('file')) {
                    // see) public\js\tinymce\plugins\file\plugin.min.js
                    var input = document.getElementById('cc-file-upload-' + meta.fieldname + '-{{$frame_id}}');

                    input.onchange = function () {
                        var file = this.files[0];
                        callback(file.name);
                    };

                    input.click();
                }
                // pdf plugin. フィールド名の先頭がpdf
                else if (meta.fieldname.startsWith('pdf')) {
                    // see) public\js\tinymce\plugins\pdf\plugin.min.js
                    var input = document.getElementById('cc-pdf-upload-{{$frame_id}}');
                    input.setAttribute('accept', '.pdf');

                    input.onchange = function () {
                        var file = this.files[0];
                        callback(file.name);
                    };

                    input.click();
                }
                // face plugin. フィールド名の先頭がface
                else if (meta.fieldname.startsWith('photo')) {
                    // see) public\js\tinymce\plugins\face\plugin.min.js
                    var input = document.getElementById('cc-face-upload-{{$frame_id}}');
                    input.setAttribute('accept', '.jpg,.png');

                    input.onchange = function () {
                        var file = this.files[0];
                        callback(file.name);
                    };

                    input.click();
                }
                // link plugin
                else if (meta.fieldname == 'url') {
                    // bugfix: iphone or ipad + safari のみ、DOM(実際のinput type file)がないと機能しないため対応
                    // var input = document.createElement('input');
                    // input.setAttribute('type', 'file');
                    var input = document.getElementById('cc-file-upload-file-{{$frame_id}}');
                    // 他でも使いまわすため、ここでクリア
                    input.value = '';

                    input.removeAttribute('accept');

                    input.onchange = function () {
                        var file = this.files[0];
                        // callback(file.name);

                        // ajax
                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;

                        xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/upload');

                        xhr.onload = function() {
                            var json;

                            if (xhr.status < 200 || xhr.status >= 300) {
                                failure('HTTP Error: ' + xhr.status);
                                return;
                            }

                            json = JSON.parse(xhr.responseText);
                            // console.log(json);

                            if (!json || typeof json.location != 'string') {
                                failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }

                            callback(json.location, {text: file.name});
                        };

                        formData = new FormData();
                        formData.append('file', file, file.name );

                        var tokens = document.getElementsByName("csrf-token");
                        formData.append('_token', tokens[0].content);
                        formData.append('page_id', {{$page_id}});
                        formData.append('plugin_name', tinymce.activeEditor.settings.cc_config.plugin_name);
                        xhr.send(formData);
                    };

                    input.click();
                }
            }

            /* Provide image and alt text for the image dialog
            --------------------------------------------- */
            if (meta.filetype == 'image') {
                // callback('myimage.jpg', {alt: 'My alt text'});

                /* and here's our custom image picker*/
                // bugfix: iphone or ipad + safari のみ、DOM(実際のinput type file)がないと機能しないため対応
                // var input = document.createElement('input');
                // input.setAttribute('type', 'file');
                var input = document.getElementById('cc-file-upload-file-{{$frame_id}}');
                // 他でも使いまわすため、ここでクリア
                input.value = '';

                // console.log(meta.fieldname);

                // change: laravelでアップできる拡張子と同じにする。see) \Illuminate\Validation\Concerns\ValidatesAttributes::validateImage()
                // input.setAttribute('accept', 'image/*');
                // input.setAttribute('accept', '.jpg, .jpeg, .jpe, .png, .gif');
                input.setAttribute('accept', '.jpeg, .jpg, .png, .gif, .bmp, .svg, .webp');

                // image plugin
                if (meta.fieldname == 'src') {
                    // 画像はimages_upload_handlerが動作するため、サンプル通りにblobCacheに追加する. (blobCacheに入れるとなんでアップロードできるか詳細わからなかった。)
                    /*
                    Note: In modern browsers input[type="file"] is functional without
                    even adding it to the DOM, but that might not be the case in some older
                    or quirky browsers like IE, so you might want to add it to the DOM
                    just in case, and visually hide it. And do not forget do remove it
                    once you do not need it anymore.
                    */
                    input.onchange = function () {
                        var file = this.files[0];

                        var reader = new FileReader();
                        reader.onload = function () {
                            /*
                            Note: Now we need to register the blob in TinyMCEs image blob
                            registry. In the next release this part hopefully won't be
                            necessary, as we are looking to handle it internally.
                            */
                            var id = 'blobid' + (new Date()).getTime();
                            var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                            var base64 = reader.result.split(',')[1];
                            var blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);

                            // 拡張子取り除き
                            var file_name = new String(file.name).substring(file.name.lastIndexOf('/') + 1);
                            if (file_name.lastIndexOf(".") != -1) {
                                file_name = file_name.substring(0, file_name.lastIndexOf("."));
                            }

                            /* call the callback and populate the Title field with the file name */
                            // callback(blobInfo.blobUri(), { title: file.name });
                            // callback(blobInfo.blobUri(), { alt: file.name });
                            callback(blobInfo.blobUri(), { alt: file_name });
                            // callback(file.name);
                        };
                        reader.readAsDataURL(file);
                    };
                }
                // media plugin
                else if (meta.fieldname == 'poster') {
                    // console.log('media');

                    // 動画のサムネイルはimages_upload_handlerが動作しないため、このタイミングでアップロードする
                    input.onchange = function () {
                        var file = this.files[0];
                        // callback(file.name);

                        // ajax
                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;

                        xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/upload');

                        xhr.onload = function() {
                            var json;

                            if (xhr.status < 200 || xhr.status >= 300) {
                                failure('HTTP Error: ' + xhr.status);
                                return;
                            }

                            json = JSON.parse(xhr.responseText);
                            // console.log(json);

                            if (!json || typeof json.location != 'string') {
                                failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }

                            callback(json.location);
                        };

                        formData = new FormData();
                        formData.append('file', file, file.name );

                        var tokens = document.getElementsByName("csrf-token");
                        formData.append('_token', tokens[0].content);
                        formData.append('page_id', {{$page_id}});
                        formData.append('plugin_name', tinymce.activeEditor.settings.cc_config.plugin_name);
                        xhr.send(formData);
                    };
                }

                input.click();
            }

            /* Provide alternative source and posted for the media dialog
            --------------------------------------------- */
            if (meta.filetype == 'media') {
                // callback('movie.mp4', {source2: 'alt.ogg', poster: 'image.jpg'});
                // console.log(meta.fieldname);

                // bugfix: iphone or ipad + safari のみ、DOM(実際のinput type file)がないと機能しないため対応
                // var input = document.createElement('input');
                // input.setAttribute('type', 'file');
                var input = document.getElementById('cc-file-upload-file-{{$frame_id}}');
                // 他でも使いまわすため、ここでクリア
                input.value = '';

                input.setAttribute('accept', '.mp4, .mp3');

                input.onchange = function () {
                    var file = this.files[0];
                    // callback(file.name);

                    // ajax
                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;

                    xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/upload');

                    xhr.onload = function() {
                        var json;

                        if (xhr.status < 200 || xhr.status >= 300) {
                            failure('HTTP Error: ' + xhr.status);
                            return;
                        }

                        json = JSON.parse(xhr.responseText);
                        // console.log(json);

                        if (!json || typeof json.location != 'string') {
                            failure('Invalid JSON: ' + xhr.responseText);
                            return;
                        }

                        // mp3か
                        if (file.name.toUpperCase().match(/\.(mp3)$/i)) {
                            // tinymce5のmedia pluginは、拡張子を見て audioタグを出力するため、苦肉の策として.mp3をつけて後で消す
                            callback(json.location + '.mp3');
                        } else {
                            callback(json.location);
                        }
                    };

                    formData = new FormData();
                    formData.append('file', file, file.name );

                    var tokens = document.getElementsByName("csrf-token");
                    formData.append('_token', tokens[0].content);
                    formData.append('page_id', {{$page_id}});
                    formData.append('plugin_name', tinymce.activeEditor.settings.cc_config.plugin_name);
                    xhr.send(formData);
                };

                input.click();
            }
        },

        media_live_embeds: true,
        media_alt_source: false,

        // 動画コールバック
        video_template_callback: function(data) {
            // videoタグ. ダウンロード禁止
            var html = '<video width="' + data.width + '" height="' + data.height + '"' + (data.poster ? ' poster="' + data.poster + '"' : '') + ' controls="controls" controlsList="nodownload">\n'
                    + '<source src="' + data.source + '"' + (data.sourcemime ? ' type="' + data.sourcemime + '"' : '') + ' />\n'
                    + (data.altsource ? '<source src="' + data.altsource + '"' + (data.altsourcemime ? ' type="' + data.altsourcemime + '"' : '') + ' />\n' : '')
                    + '</video>';
            return html;
        },

        // 音声コールバック
        audio_template_callback: function(data) {
            // audioタグを吐かせるために追加した末尾.mp3を消す
            var source = data.source;
            source = source.replace(new RegExp(".mp3$"), "");

            // audioタグ. ダウンロード禁止
            var html = '<audio controls controlsList="nodownload">'
                + '\n<source src="' + source + '"' + (data.sourcemime ? ' type="' + data.sourcemime + '"' : '') + ' />\n'
                + (data.altsource ? '<source src="' + data.altsource + '"' + (data.altsourcemime ? ' type="' + data.altsourcemime + '"' : '') + ' />\n' : '')
                + '</audio>';
            return html;
        },

        // delete: 不要なためコメントアウト
        // image_caption: true,
        // image_title: true,

        // 画像プラグイン＞アップロード（タブ）非表示. アップロード（タブ）で画像アップロードすると即時アップロードされ、一般（タブ）のリサイズのパラメータが拾えず全て原寸でアップロードされるため、使わない。
        image_uploadtab: false,

        image_class_list: [
            {title: 'Responsive', value: 'img-fluid'},
            {title: '枠線＋Responsive', value: 'img-fluid img-thumbnail'},
            {title: 'None', value: 'none'},
        ],
        invalid_styles: {
            'table': 'height width border-collapse',
            'tr': 'height width',
            'th': 'height width',
            'td': 'height width',
        },
        //table_resize_bars: false,
        //object_resizing: 'img',
        //table_default_attributes: {
        //    class: 'table'
        //},
        {{-- テーブル --}}
        {!!$table_class_list_file!!}

        {{-- テーブルセル --}}
        {!!$table_cell_class_list_file!!}

        // 画像アップロード・ハンドラ
        // change: tinymce5対応
        // images_upload_handler: function (blobInfo, success, failure) {
        images_upload_handler: function (blobInfo, success, failure, progress) {
            var xhr, formData;

            // AJAX
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '{{url('/')}}/upload');

            xhr.upload.onprogress = function (e) {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = function() {
                var json;

                // アップロード後に押せない全ボタンを解除する
                $(':button').prop('disabled', false);
                // console.log("転送が完了しました。");

                if (xhr.status === 403) {
                    failure('HTTP Error: ' + xhr.status, { remove: true });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }

                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                success(json.location);
            };

            // アップロード中は全ボタンを押させない
            $(':button').prop('disabled', true);
            // console.log("転送開始");

            xhr.onerror = function () {
                failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            formData = new FormData();

            // bugfix: 「blobInfo.blob().name」は新規のアップロードの際しか名前が設定されないが、「blobInfo.filename()」は新規の時も回転などimagetoolsを使用した時も
            // 常に設定されているので、typeofの評価は不要で常に fileName = blobInfo.filename(); でよいのではと思います。
            // https://github.com/opensource-workshop/connect-cms/pull/353#issuecomment-636411186
            //
            // if( typeof(blobInfo.blob().name) !== undefined )
            //     fileName = blobInfo.blob().name;
            // else
            //     fileName = blobInfo.filename();
            fileName = blobInfo.filename();
            // console.log(blobInfo);

            // リサイズ用
            var frame_id = tinymce.activeEditor.settings.cc_config.frame_id;
            var resize = document.getElementById('cc-resized-image-size-' + frame_id).value;

            var tokens = document.getElementsByName("csrf-token");
            formData.append('_token', tokens[0].content);
            formData.append('image', blobInfo.blob(), fileName);
            formData.append('resize', resize);
            formData.append('page_id', {{$page_id}});
            formData.append('plugin_name', tinymce.activeEditor.settings.cc_config.plugin_name);

            xhr.send(formData);

            // クリア
            document.getElementById('cc-resized-image-size-' + frame_id).value = '';
        },

        // Connect-CMS独自設定
        cc_config: {
            frame_id: '{{$frame_id}}',
            plugin_name: '{{$frame->plugin_name ?? ''}}',
            upload_max_filesize_caption: '※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}',
            // PDFサムネイルの大きさ 選択肢、初期値
            width_of_pdf_thumbnails_items: {!!  WidthOfPdfThumbnail::getWysiwygListBoxItems()  !!},
            width_of_pdf_thumbnails_initial: '{{  Configs::getConfigsValue($cc_configs, "width_of_pdf_thumbnails_initial", WidthOfPdfThumbnail::getDefault())  }}',
            // PDFサムネイルの数 選択肢、初期値
            number_of_pdf_thumbnails_items: {!!  NumberOfPdfThumbnail::getWysiwygListBoxItems()  !!},
            number_of_pdf_thumbnails_initial: '{{  Configs::getConfigsValue($cc_configs, "number_of_pdf_thumbnails_initial", NumberOfPdfThumbnail::getDefault())  }}',
            // 画像プラグイン＞画像サイズ 選択肢、初期値
            resized_image_size_items: {!!  ResizedImageSize::getWysiwygListBoxItems()  !!},
            resized_image_size_initial: '{{  Configs::getConfigsValue($cc_configs, "resized_image_size_initial", ResizedImageSize::getDefault())  }}',
            // 画像プラグイン＞画像サイズを表示するか
            has_image_resize: {{  function_exists('gd_info') ? 'true' : 'false' }},
        },

        setup: function(editor) {
            // see) events https://www.tiny.cloud/docs/advanced/events/
            // see) editor https://www.tiny.cloud/docs/api/tinymce/tinymce.editor/

            // bugfix: IE11でウィジウィグが動作しないバグ修正
            // editor.on('ExecCommand', (event) => {
            editor.on('ExecCommand', function (event) {
                const command = event.command;
                // console.log(event.command);
                // console.log(editor.settings.cc_config.upload_max_filesize_caption);

                // image plugin の保存ボタン押下後イベント
                if (command === 'mceUpdateImage') {
                    // console.log(jQuery('.tox-textfield')[2].value);
                    // console.log(jQuery('.tox-textfield')[3].value);
                    var frame_id = editor.settings.cc_config.frame_id;

                    // [TODO] 画像プラグイン＞アップロード（タブ）で「保存」を押下すると、下記要素がとれずJSエラーになる。
                    //        アップロード後は、画像プラグイン＞一般（タブ）に自動遷移するため、運用上の問題は発生しないと思われるため、JSエラーはとりあえずそのままにする。
                    //        エラーが出ないように要素が無い場合、処理しない事を検討したが、その場合、リサイズされなくなるため、対応を見送った。

                    // リサイズ画像サイズをinput type=textに保持
                    document.getElementById('cc-resized-image-size-' + frame_id).value = jQuery('.tox-listbox--select')[0].dataset.value
                }
            });

            // bugfix: IE11でウィジウィグが動作しないバグ修正
            // editor.on('OpenWindow', (event) => {
            editor.on('OpenWindow', function (event) {
                // console.log(event);
                // console.log('OpenWindow', event.dialog);
                // console.log('OpenWindow', event.dialog.getData());
                // console.log(jQuery('.tox-dialog__title')[0].textContent);

                var title = jQuery('.tox-dialog__title')[0].textContent;

                // [TODO] image plugin, media pluginはタブ遷移でメッセージ消える. 対応方法わかれば今後対応
                // media plugin, link plugin
                if (title === 'メディアの挿入・編集' || title === 'リンクの挿入・編集') {
                    // 新しいHTML要素を作成
                    var div = document.createElement('div');
                    div.setAttribute('style', 'font-size: 14px;');
                    div.textContent = editor.settings.cc_config.upload_max_filesize_caption;

                    // 指定した要素の後に挿入
                    jQuery('.tox-form__controls-h-stack')[0].after(div);
                }
            });
        }
    });
</script>
