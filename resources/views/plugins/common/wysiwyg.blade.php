{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@php
    // php8.0 Warning対応
    $frame_id = $frame_id ?? null;
    $frame = $frame ?? new \App\Models\Common\Frame();
    $page_id = $page_id ?? 0;
    $theme_group_default = $theme_group_default ?? null;
    $theme = $theme ?? null;

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

    // ディレクトリインストール時のcontent_css:のパス修正
    $appUrl = config('app.url');
    $appUrl = rtrim($appUrl, '/'); // 末尾の '/' を削除
    $urlParts = parse_url($appUrl);
    $path = isset($urlParts['path']) ? $urlParts['path'] : '';
    // パスが存在し、かつ '/' で終わらない場合はディレクトリインストールと見なす
    $isDirectoryInstall = !empty($path) && substr($path, -1) !== '/';
    if ($isDirectoryInstall) {
        // 引用符で囲まれた文字列を取得
        preg_match_all('/"([^"]*)"/', $content_css_file, $matches);
        // $matches[1] の0番目の要素を取得
        $firstMatch = isset($matches[1][0]) ? $matches[1][0] : null;
        if ($firstMatch !== null) {
            $dataArray = explode(', ', $firstMatch);
            // 各データの先頭に変数を追加
            $modifiedDataArray = array_map(function ($item) use ($path) {
                return $path . $item;
            }, $dataArray);
            $modifiedFirstMatch = implode(', ', $modifiedDataArray);
            // content_cssに追記する
            $content_css_file = 'content_css: "' . $modifiedFirstMatch . '",';
        }
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
    // change: tinymce5対応. textcolor は coreに含まれたため除外
    // change: tinymce6対応. imagetools は オープンソース版から削除のため除外
    // change: tinymce6対応. hr は coreに含まれたため除外
    $plugins = 'file image media link autolink preview code table lists advlist template ';
    if (Configs::getConfigsValue($cc_configs, 'use_translate', UseType::not_use) == UseType::use) {
        $plugins .= ' translate';
    }
    if (Configs::getConfigsValue($cc_configs, 'use_pdf_thumbnail')) {
        $plugins .= ' pdf';
    }
    // AI顔認識
    if (Configs::getConfigsValue($cc_configs, 'use_face_ai')) {
        $plugins .= ' face';
    }

    $plugins = "plugins  : '" . $plugins . "',";

    // 文字サイズの選択
    $toolbar_fontsizeselect = '';
    if (Configs::getConfigsValue($cc_configs, 'fontsizeselect')) {
        $toolbar_fontsizeselect = '| fontsize';
    }

    // toolbar
    $toolbar = "undo redo | bold italic underline strikethrough subscript superscript {$toolbar_fontsizeselect} | styles | forecolor backcolor | removeformat | table hr | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | link | image file media | preview | code";
    $mobile_toolbar = "undo redo | image file media | link | code | bold italic underline strikethrough subscript superscript {$toolbar_fontsizeselect} | styles | forecolor backcolor | removeformat | table hr | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | preview";
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
    // bugfix: AI顔認識は常時ONではなく、外部サービス設定ON時に有効
    if (Configs::getConfigsValue($cc_configs, 'use_face_ai')) {
        // AI顔認識
        $toolbar .= ' face ';
        $mobile_toolbar .= 'face ';
    }

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
@endphp

{{-- 非表示のinput type file. file plugin用. see) resources\js\tinymce\plugins\file\plugin.js --}}
<input type="file" class="d-none" id="cc-file-upload-file1-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file2-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file3-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file4-{{$frame_id}}">
<input type="file" class="d-none" id="cc-file-upload-file5-{{$frame_id}}">

{{-- 非表示のinput type file. pdf plugin用. see) resources\js\tinymce\plugins\pdf\plugin.js --}}
<input type="file" class="d-none" id="cc-pdf-upload-{{$frame_id}}">

{{-- 非表示のinput type file. face plugin用. see) resources\js\tinymce\plugins\face\plugin.js --}}
<input type="file" class="d-none" id="cc-face-upload-{{$frame_id}}">

{{-- bugfix: iphone or ipad + safari のみ、DOM(実際のinput type file)がないと機能しないため対応
    see) https://stackoverflow.com/questions/47664777/javascript-file-input-onchange-not-working-ios-safari-only --}}
<input type="file" class="d-none" id="cc-file-upload-file-{{$frame_id}}">

{{-- 登録時のリサイズ用 --}}
<input type="text" class="d-none" id="cc-resized-image-size-{{$frame_id}}">

<script type="text/javascript">
    tinymce.init({
        @if(isset($target_class) && $target_class)
            selector : 'textarea.{{$target_class}}',
        @else
            selector : 'textarea',
        @endif

        cache_suffix: '?v=6.1',

        // add: tinymce6対応. www.tiny.cloudのPRリンク表示OFF
        promotion: false,

        // change: app.blade.phpと同様にlocaleを見て切替
        language : '{{ app()->getLocale() }}',

        document_base_url : '{{url("/")}}',

        @if(isset($readonly) && $readonly)
            readonly : 1,
        @endif

        @if(isset($use_br) && $use_br)
            // 改行を p タグから br タグに変更
            newline_behavior: 'invert',
        @endif

        {{-- plugins --}}
        {!!$plugins!!}

        {{-- styles = 書式 --}}
        {!!$toolbar!!}

        // add: tinymce6対応. npmでインストールしたskin対応
        skin_url: 'default',

        // font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt',
        font_size_formats: '65%=0.65rem 85%=0.85rem 100%=1rem 115%=1.15rem 130%=1.3rem 150%=1.5rem 200%=2rem 300%=3rem',

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
        toolbar_mode : '{{$pc_toolbar_mode}}',
        mobile: {
            toolbar_mode : 'floating',

            {{-- styles = 書式 --}}
            {!!$mobile_toolbar!!}
        },

        relative_urls : false,
        height: {{ isset($height) ? $height : 300 }},
        resize: 'both',
        branding: false,
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
        // see) https://www.tiny.cloud/docs/tinymce/6/file-image-upload/#file_picker_callback
        file_picker_callback: function(callback, value, meta) {
            // console.log(meta.filetype, meta.fieldname);

            /* Provide file and text for the link dialog
            --------------------------------------------- */
            if (meta.filetype == 'file') {
                // callback('mypage.html', {text: 'My text'});

                // file plugin. フィールド名の先頭がfileであれば. file1～5
                if (meta.fieldname.indexOf('file') === 0) {
                //if (meta.fieldname.startsWith('file')) {
                    // see) resources\js\tinymce\plugins\file\plugin.js
                    var input = document.getElementById('cc-file-upload-' + meta.fieldname + '-{{$frame_id}}');

                    input.onchange = function () {
                        var file = this.files[0];
                        callback(file.name);
                    };

                    input.click();
                }
                // pdf plugin. フィールド名の先頭がpdf
                else if (meta.fieldname.indexOf('pdf') === 0) {
                //else if (meta.fieldname.startsWith('pdf')) {
                    // see) resources\js\tinymce\plugins\pdf\plugin.js
                    var input = document.getElementById('cc-pdf-upload-{{$frame_id}}');
                    input.setAttribute('accept', '.pdf');

                    input.onchange = function () {
                        var file = this.files[0];
                        callback(file.name);
                    };

                    input.click();
                }
                // face plugin. フィールド名の先頭がface
                else if (meta.fieldname.indexOf('photo') === 0) {
                //else if (meta.fieldname.startsWith('photo')) {
                    // see) resources\js\tinymce\plugins\face\plugin.js
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
                                console.log('HTTP Error: ' + xhr.status);
                                return;
                            }

                            json = JSON.parse(xhr.responseText);
                            // console.log(json);

                            if (!json || typeof json.location != 'string') {
                                console.log('Invalid JSON: ' + xhr.responseText);
                                return;
                            }

                            callback(json.location, {text: file.name});
                        };

                        formData = new FormData();
                        formData.append('file', file, file.name );

                        var tokens = document.getElementsByName("csrf-token");
                        formData.append('_token', tokens[0].content);
                        formData.append('page_id', {{$page_id}});
                        formData.append('plugin_name', tinymce.activeEditor.options.get('cc_config').plugin_name);
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
                                console.log('HTTP Error: ' + xhr.status);
                                return;
                            }

                            json = JSON.parse(xhr.responseText);
                            // console.log(json);

                            if (!json || typeof json.location != 'string') {
                                console.log('Invalid JSON: ' + xhr.responseText);
                                return;
                            }

                            callback(json.location);
                        };

                        formData = new FormData();
                        formData.append('file', file, file.name );

                        var tokens = document.getElementsByName("csrf-token");
                        formData.append('_token', tokens[0].content);
                        formData.append('page_id', {{$page_id}});
                        formData.append('plugin_name', tinymce.activeEditor.options.get('cc_config').plugin_name);
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
                            console.log('HTTP Error: ' + xhr.status);
                            return;
                        }

                        json = JSON.parse(xhr.responseText);
                        // console.log(json);

                        if (!json || typeof json.location != 'string') {
                            console.log('Invalid JSON: ' + xhr.responseText);
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
                    formData.append('plugin_name', tinymce.activeEditor.options.get('cc_config').plugin_name);
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

        // 画像プラグイン＞アップロード（タブ）非表示. アップロード（タブ）で画像アップロードすると即時アップロードされ、一般（タブ）のリサイズのパラメータが拾えず全て原寸でアップロードされるため、使わない。
        image_uploadtab: false,

        // 画像の詳細設定タブ機能
        image_advtab: true,

        image_class_list: [
            {title: 'Responsive', value: 'img-fluid'},
            {title: '枠線＋Responsive', value: 'img-fluid img-thumbnail'},
            {title: 'None', value: 'none'},
        ],
        invalid_styles: {
            // 'table': 'height width border-collapse',
            // 'th': 'height width',
            // 'td': 'height width',
            'table': 'height',
            'tr': 'height width',
            'th': 'height',
            'td': 'height',
        },
        // see) https://www.tiny.cloud/docs/tinymce/latest/table-options/#table_resize_bars
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
        // change: tinymce6対応
        // see) https://www.tiny.cloud/docs/tinymce/6/upload-images/#example-using-images_upload_handler
        // images_upload_handler: function (blobInfo, success, failure, progress) {
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            var xhr, formData;

            // AJAX
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '{{url('/')}}/upload');

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                var json;

                // アップロード後に押せない全ボタンを解除する
                $(':button').prop('disabled', false);
                // console.log("転送が完了しました。");

                if (xhr.status === 403) {
                    reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status);
                    return;
                }

                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                resolve(json.location);
            };

            // アップロード中は全ボタンを押させない
            $(':button').prop('disabled', true);
            // console.log("転送開始");

            xhr.onerror = function () {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            formData = new FormData();

            // bugfix: 「blobInfo.blob().name」は新規のアップロードの際しか名前が設定されないが、「blobInfo.filename()」は新規の時も回転などimagetools(今はなし)を使用した時も
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
            var frame_id = tinymce.activeEditor.options.get('cc_config').frame_id;
            var resize = document.getElementById('cc-resized-image-size-' + frame_id).value;

            var tokens = document.getElementsByName("csrf-token");
            formData.append('_token', tokens[0].content);
            formData.append('image', blobInfo.blob(), fileName);
            formData.append('resize', resize);
            formData.append('page_id', {{$page_id}});
            formData.append('plugin_name', tinymce.activeEditor.options.get('cc_config').plugin_name);

            xhr.send(formData);

            // クリア
            document.getElementById('cc-resized-image-size-' + frame_id).value = '';
        }),

        setup: function(editor) {
            // see) events https://www.tiny.cloud/docs/advanced/events/
            // see) editor https://www.tiny.cloud/docs/api/tinymce/tinymce.editor/

            // Connect-CMS独自設定
            // see) editor.options https://www.tiny.cloud/docs/tinymce/latest/apis/tinymce.editoroptions/
            editor.options.register('cc_config', {
                processor: 'object',
                default: {
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
                    // AI顔認識の画像サイズ・粗さ
                    face_image_sizes: {!! ResizedImageSize::getWysiwygListBoxItems('asis') !!},
                    face_image_initial: '{{ Configs::getConfigsValue($cc_configs, "face_ai_initial_size", "middle") }}',
                    finenesses: {!! Fineness::getWysiwygListBoxItems() !!},
                    fineness_initial: '{{ Configs::getConfigsValue($cc_configs, "face_ai_initial_fineness", Fineness::getDefault()) }}'
                },
            });
            // console.log(editor.options.get('cc_config').frame_id);

            // bugfix: IE11でウィジウィグが動作しないバグ修正
            // editor.on('ExecCommand', (event) => {
            editor.on('ExecCommand', function (event) {
                const command = event.command;
                // console.log(event.command);

                // image plugin の保存ボタン押下後イベント
                if (command === 'mceUpdateImage') {
                    // console.log(jQuery('.tox-textfield')[2].value);
                    // console.log(jQuery('.tox-textfield')[3].value);
                    var frame_id = editor.options.get('cc_config').frame_id;

                    // リサイズ画像サイズをinput type=textに保持
                    document.getElementById('cc-resized-image-size-' + frame_id).value = event.value.resize;
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
                if (title === 'メディアの挿入/編集' || title === 'リンクの挿入/編集') {
                    // 新しいHTML要素を作成
                    var div = document.createElement('div');
                    div.setAttribute('style', 'font-size: 14px;');
                    div.textContent = editor.options.get('cc_config').upload_max_filesize_caption;

                    // 指定した要素の後に挿入
                    jQuery('.tox-form__controls-h-stack')[0].after(div);
                }
            });
        }
    });
</script>
