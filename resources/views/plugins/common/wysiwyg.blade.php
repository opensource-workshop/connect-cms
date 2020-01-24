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
    if (File::exists($style_formats_path)) {
        $style_formats_file = File::get($style_formats_path);
    }

    // テーマ固有スタイル
    $block_formats_file = '';
    $block_formats_path = public_path() . '/themes/' . $theme . '/wysiwyg/block_formats.txt';
    if (File::exists($style_formats_path)) {
        $block_formats_file = File::get($block_formats_path);
    }

    // CSS
    $content_css_file = '';
    $content_css_path = public_path() . '/themes/' . $theme . '/wysiwyg/content_css.txt';
    if (File::exists($content_css_path)) {
        $content_css_file = File::get($content_css_path);
    }

    // テーブルセル
    $table_cell_class_list_file = '';
    $table_cell_class_list_path = public_path() . '/themes/' . $theme . '/wysiwyg/table_cell_class_list.txt';
    if (File::exists($table_cell_class_list_path)) {
        $table_cell_class_list_file = File::get($table_cell_class_list_path);
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
@endphp
<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    tinymce.init({
        selector : 'textarea',
        language : 'ja',
        plugins  : 'file image link autolink preview textcolor code table lists',

        {{-- formatselect = スタイル, styleselect = 書式 --}}
        toolbar  : 'bold italic underline strikethrough subscript superscript | formatselect | styleselect | forecolor backcolor | table | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | link jbimages | image file |  preview | code ',

        {{-- テーマ固有書式 --}}
        {!!$style_formats_file!!}

        {{-- テーマ固有スタイル --}}
        {!!$block_formats_file!!}

        menubar  : '',
        relative_urls : false,
        height: 300,
        branding: false,
        //forced_root_block : false,
        valid_children : "+body[style],+a[div|p],",
        extended_valid_elements : "script[type|charset]"
                                 +",div[*]"
                                 +",cc[*]",
        //extended_valid_elements : "script[type|charset|async|src],cc[value]",

        {{-- CSS --}}
        {!!$content_css_file!!}

        body_class : "{{$body_class}}",

        // file_picker_types: 'file image media',
        // media_live_embeds: true,
        image_caption: true,
        image_title: true,
        image_class_list: [
            {title: 'Responcie', value: 'img-fluid'},
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
        table_class_list: [
            {title: 'なし', value: ''},
        ],

        {{-- テーブルセル --}}
        {!!$table_cell_class_list_file!!}

        // 画像アップロード・ハンドラ
        images_upload_handler: function (blobInfo, success, failure) {
            var xhr, formData;
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/upload');

            xhr.onload = function() {
                var json;

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

            formData = new FormData();
            if( typeof(blobInfo.blob().name) !== undefined )
                fileName = blobInfo.blob().name;
            else
                fileName = blobInfo.filename();

            var tokens = document.getElementsByName("csrf-token");
            formData.append('_token', tokens[0].content);
            formData.append('file', blobInfo.blob(), fileName);
            xhr.send(formData);
        }
    });
</script>
