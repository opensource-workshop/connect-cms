tinymce.PluginManager.add('pdf', function(editor, url) {
    // アイコン see) https://fontawesome.com/icons/file-pdf?f=classic&s=regular
    editor.ui.registry.addIcon('pdf', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 464l48 0 0 48-48 0c-35.3 0-64-28.7-64-64L0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 304l-48 0 0-144-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16zM176 352l32 0c30.9 0 56 25.1 56 56s-25.1 56-56 56l-16 0 0 32c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-48 0-80c0-8.8 7.2-16 16-16zm32 80c13.3 0 24-10.7 24-24s-10.7-24-24-24l-16 0 0 48 16 0zm96-80l32 0c26.5 0 48 21.5 48 48l0 64c0 26.5-21.5 48-48 48l-32 0c-8.8 0-16-7.2-16-16l0-128c0-8.8 7.2-16 16-16zm32 128c8.8 0 16-7.2 16-16l0-64c0-8.8-7.2-16-16-16l-16 0 0 96 16 0zm80-112c0-8.8 7.2-16 16-16l48 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 32 32 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 48c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64 0-64z"/></svg>');

    // プラグインボタンの追加
    // see) https://www.tiny.cloud/docs/advanced/creating-a-plugin/
    editor.ui.registry.addButton("pdf", {
        icon: "pdf",
        tooltip: "PDFアップロード",
        onAction: pluginWin,

        onPostRender: function() { // プラグイン要素選択時プラグインボタンアクティブ
            var _this = this;
            editor.on("NodeChange", function(e) {
                var is_active = jQuery( editor.selection.getNode() ).hasClass("plugin");
                _this.active( is_active );
            })

            editor.on("DblClick", function(e) {
                if ( e.target.className == "plugin" ) {
                    pluginWin(e.toElement.innerText);
                }
            })
        },
    });

    function pluginWin(e) { // プラグインウィンドウを開く関数
        tinymce.activeEditor.windowManager.open({
            title: "PDFアップロード",
            body: {
                type: 'panel',
                items: [
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#alertbanner
                        type: 'alertbanner',
                        level: 'info',
                        text: 'PDFからサムネイル画像を自動作成します。',
                        icon: 'info',
                    },
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#urlinput
                        type: "urlinput",
                        name: "pdf",
                        filetype: 'file', // allow any file types
                        label: 'PDF'
                    },
                    {
                        // アップロードできる最大サイズのキャプション
                        type: 'collection',
                        name: 'upload_max_filesize_caption', // identifier
                        label: editor.options.get('cc_config').upload_max_filesize_caption
                    },
                    {
                        type: 'listbox',
                        name: 'width_of_pdf_thumbnails', // identifier
                        label: 'サムネイルの大きさ',
                        disabled: false,
                        items: editor.options.get('cc_config').width_of_pdf_thumbnails_items,
                    },
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#listbox
                        type: 'listbox',
                        name: 'number_of_pdf_thumbnails', // identifier
                        label: 'サムネイルの数',
                        disabled: false,
                        items: editor.options.get('cc_config').number_of_pdf_thumbnails_items,
                    },
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#input
                        type: 'input',
                        name: 'pdf_password',
                        inputMode: 'text',
                        label: 'PDFパスワード',
                        placeholder: '',
                        disabled: false,
                        maximized: false
                    },
                    {
                        type: 'collection',
                        name: 'pdf_password_caption', // identifier
                        label: '※ パスワード付PDFの場合、入力してください。'
                    }
                ]
            },
            // 初期値設定
            initialData: {
                width_of_pdf_thumbnails: editor.options.get('cc_config').width_of_pdf_thumbnails_initial,
                number_of_pdf_thumbnails: editor.options.get('cc_config').number_of_pdf_thumbnails_initial,
            },
            buttons: [
                {
                    type: 'cancel',
                    text: 'Close'
                },
                {
                    type: 'submit',
                    text: 'Save',
                    primary: true
                }
            ],
            // OKボタンをクリックした際の挙動
            onSubmit: function(api) {
                // AJAX
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/upload');

                xhr.onload = function() {
                    var json;

                    if (xhr.status < 200 || xhr.status >= 300) {
                        // コンソールのエラー出力
                        console.error('HTTP Error: ' + xhr.status);
                        return;
                    }

                    // jsonエラーは、JSON.parse()で発生するため、特別処理を入れる必要なし
                    json = JSON.parse(xhr.responseText);
                    // console.log(json);
                    // console.log(json.link_text);

                    // 何もアップロードしないと json = [] で返ってくるため対応
                    if (typeof json.link_text !== "undefined") {
                        // サーバから戻ってきたHTMLをエディタ画面に挿入する。
                        editor.insertContent(json.link_text);
                    }

                    // １画面に複数ウィジウィグがあるプラグイン(blog, database)のために、ここでクリア
                    document.getElementById('cc-pdf-upload-' + frame_id).value = '';

                    // 閉じる
                    api.close();
                };

                var tokens = document.getElementsByName("csrf-token");
                var page_id = document.getElementsByName("_page_id");
                var data = api.getData();

                // var frame_id = document.getElementsByName("frame_id")[0].value;
                var frame_id = editor.options.get('cc_config').frame_id;

                formData = new FormData();
                formData.append('_token', tokens[0].content);
                formData.append('page_id', page_id[0].content);
                formData.append('plugin_name', editor.options.get('cc_config').plugin_name);
                // tinymce5で input type fileが無くなったため、wysiwyg.blade.phpに用意した非表示のinput type fileを使って送信
                formData.append('pdf', document.getElementById('cc-pdf-upload-' + frame_id).files[0]);
                formData.append('pdf_password', data.pdf_password);
                formData.append('width_of_pdf_thumbnails', data.width_of_pdf_thumbnails);
                formData.append('number_of_pdf_thumbnails', data.number_of_pdf_thumbnails);

                // console.log(document.getElementById('cc-pdf-upload-' + frame_id).files.length);

                xhr.send(formData);
            }
        });
    } // function pluginWin()
});
