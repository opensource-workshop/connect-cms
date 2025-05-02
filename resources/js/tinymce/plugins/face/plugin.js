tinymce.PluginManager.add('face', function(editor, url) {
    // アイコン see) https://fontawesome.com/icons/face-grin-stars?f=classic&s=regular
    editor.ui.registry.addIcon('face', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM183.2 132.6c-1.3-2.8-4.1-4.6-7.2-4.6s-5.9 1.8-7.2 4.6l-16.6 34.7-38.1 5c-3.1 .4-5.6 2.5-6.6 5.5s-.1 6.2 2.1 8.3l27.9 26.5-7 37.8c-.6 3 .7 6.1 3.2 7.9s5.8 2 8.5 .6L176 240.5l33.8 18.3c2.7 1.5 6 1.3 8.5-.6s3.7-4.9 3.2-7.9l-7-37.8L242.4 186c2.2-2.1 3.1-5.3 2.1-8.3s-3.5-5.1-6.6-5.5l-38.1-5-16.6-34.7zm160 0c-1.3-2.8-4.1-4.6-7.2-4.6s-5.9 1.8-7.2 4.6l-16.6 34.7-38.1 5c-3.1 .4-5.6 2.5-6.6 5.5s-.1 6.2 2.1 8.3l27.9 26.5-7 37.8c-.6 3 .7 6.1 3.2 7.9s5.8 2 8.5 .6L336 240.5l33.8 18.3c2.7 1.5 6 1.3 8.5-.6s3.7-4.9 3.2-7.9l-7-37.8L402.4 186c2.2-2.1 3.1-5.3 2.1-8.3s-3.5-5.1-6.6-5.5l-38.1-5-16.6-34.7zm6.3 175.8c-28.9 6.8-60.5 10.5-93.6 10.5s-64.7-3.7-93.6-10.5c-18.7-4.4-35.9 12-25.5 28.1c24.6 38.1 68.7 63.5 119.1 63.5s94.5-25.4 119.1-63.5c10.4-16.1-6.8-32.5-25.5-28.1z"/></svg>');

    // プラグインボタンの追加
    // see) https://www.tiny.cloud/docs/advanced/creating-a-plugin/
    editor.ui.registry.addButton("face", {
        icon: "face",
        tooltip: "AI顔認識",
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
            title: "AI顔認識",
            body: {
                type: 'panel',
                items: [
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#alertbanner
                        type: 'alertbanner',
                        level: 'info',
                        text: '写真の顔をAIで判断して、モザイク処理を施します。',
                        icon: 'info',
                    },
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#urlinput
                        type: "urlinput",
                        name: "photo",
                        filetype: 'file', // allow any file types
                        label: 'jpg, png 形式の画像ファイル'
                    },
                        // アップロードできる最大サイズのキャプション
                    {
                        type: 'collection',
                        name: 'upload_max_filesize_caption', // identifier
                        label: editor.options.get('cc_config').upload_max_filesize_caption
                    },
                    {
                        // 代替テキスト
                        type: 'input',
                        name: 'alt',
                        inputMode: 'text',
                        label: '代替テキスト',
                        placeholder: '',
                        disabled: false,
                        maximized: false
                    },
                    {
                        type: 'listbox',
                        name: 'image_size', // identifier
                        label: '画像サイズ（最大でこの大きさに縮小されます）',
                        disabled: false,
                        items: editor.options.get('cc_config').face_image_sizes
                    },
                    {
                        type: 'listbox',
                        name: 'mosaic_fineness', // identifier
                        label: 'モザイクの粗さ',
                        disabled: false,
                        items: editor.options.get('cc_config').finenesses
                    }
                ]
            },
            // 初期値設定
            initialData: {
                image_size: editor.options.get('cc_config').face_image_initial,
                mosaic_fineness: editor.options.get('cc_config').fineness_initial
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
                xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/upload/face');

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
                    document.getElementById('cc-face-upload-' + frame_id).value = '';

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
                formData.append('photo', document.getElementById('cc-face-upload-' + frame_id).files[0]);
                formData.append('alt', data.alt);
                formData.append('image_size', data.image_size);
                formData.append('mosaic_fineness', data.mosaic_fineness);

                xhr.send(formData);
            }
        });
    } // function pluginWin()
});
