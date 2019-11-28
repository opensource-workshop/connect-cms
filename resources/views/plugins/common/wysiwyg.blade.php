{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
 --}}
<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    tinymce.init({
        selector : 'textarea',
        language : 'ja',
        // plugins  : 'file media image jbimages link autolink preview textcolor code table lists',
        // toolbar  : 'plugin insert media image bold italic underline strikethrough subscript superscript | formatselect | forecolor backcolor | table | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages | preview | code ',
        plugins  : 'file image link autolink preview textcolor code table lists',
        toolbar  : 'bold italic underline strikethrough subscript superscript | formatselect | forecolor backcolor | table | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | link jbimages | image file |  preview | code ',
        // オリジナルCSSのサンプル
        // toolbar  : 'bold italic underline strikethrough subscript superscript | styleselect | formatselect | forecolor backcolor | table | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | outdent indent | link jbimages | image file |  preview | code ',
        block_formats: "スタイル=p;見出し1=h1;見出し2=h2;見出し3=h3;見出し4=h4;見出し5=h5;見出し6=h6",
        menubar  : '',
        relative_urls : false,
        height: 300,
        branding: false,
        valid_children : "+body[style],+a[div|p]",
        extended_valid_elements : "script[type|charset]",
        // オリジナルCSSのサンプル
        //style_formats : [
        //    {title : 'オリジナルボタン', inline : 'span', classes : 'btn-square'},
        //],
        // オリジナルCSSの読み込み
        //content_css: "/themes/opac/themes.css",

        // file_picker_types: 'file image media',
        // media_live_embeds: true,
        image_caption: true,
        image_title: true,

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
