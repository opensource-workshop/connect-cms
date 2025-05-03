tinymce.PluginManager.add('file', function(editor, url) {
    // add: tinymce5対応
    // アイコン see) https://fontawesome.com/icons/paperclip?f=classic&s=solid
    editor.ui.registry.addIcon('file', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z"/></svg>');

    // プラグインボタンの追加
    // change: tinymce5対応 see) https://www.tiny.cloud/docs/advanced/creating-a-plugin/
    editor.ui.registry.addButton("file", {
        icon: "file",
        tooltip: "ファイルアップロード",
        // change: tinymce5対応
        onAction: pluginWin, // コールバック関数

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
        // eはボタンをクリックした場合オブジェクト、プラグインのコンテンツをダブルクリックした際は文字列が入るので判定のため文字列に一旦変換する
        if (String(e).match(/^(&|#)/)) {

            // プラグインの形式に対応する正規表現（暫定）。
            // ややこしいことにインラインテキストのある無しや{}を二重にしたり、改行の有無最後のセミコロンなしでも動くプラグインがあるなど対応が複雑
            var m = e.match(/^(&|#)(\w+)\((.*)\)([\s\S]*)?/);

            if(typeof(m[4]) !== "undefined"){
                if (m[4].match(/;$/)) { m[4] = m[4].replace(/;$/g, "") }; // インライン要素のあるなしにかかわらず行末の;は削除
            }
            //var inline_block_value = (m[1] === "&") ? "inline" : "block";
        } else {
            var m = new Array("", "", "", "", ""); // ボタンをクリックした際は空のデフォルト値を指定
        }
        tinymce.activeEditor.windowManager.open({
            title: "ファイルアップロード",
            width: 600,
            height: 250,
            // change: tinymce5対応
            body: {
                type: 'panel',
                items: [
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#urlinput
                        type: "urlinput",
                        name: "file1",
                        filetype: 'file', // allow any file types
                        label: 'ファイル1'
                    },{
                        type: "urlinput",
                        name: "file2",
                        filetype: 'file', // allow any file types
                        label: 'ファイル2'
                    },{
                        type: "urlinput",
                        name: "file3",
                        filetype: 'file', // allow any file types
                        label: 'ファイル3'
                    },{
                        type: "urlinput",
                        name: "file4",
                        filetype: 'file', // allow any file types
                        label: 'ファイル4'
                    },{
                        type: "urlinput",
                        name: "file5",
                        filetype: 'file', // allow any file types
                        label: 'ファイル5'
                    },
                    // アップロードできる最大サイズのキャプション
                    {
                        type: 'collection', // component type
                        name: 'upload_max_filesize_caption', // identifier
                        label: editor.options.get('cc_config').upload_max_filesize_caption
                    }
                ]
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
            // change: tinymce5対応
            // onSubmit: function(e) {
            onSubmit: function(api) {
                //if (e.data.plugin_name === "") { // 入力値のバリデーション（簡易版）
                //	tinyMCE.activeEditor.windowManager.alert("プラグイン名が入力されていません。");
                //	return;
                //}

                // var data = api.getData();
                /* Insert content when the window form is submitted */
                // editor.insertContent('Title: ' + data.title);

                // AJAX
                //alert("XMLHttpRequest");
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                //xhr.open('POST', '/debug/postTest.php');
                // change: tinymce5対応
                xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/upload');

                xhr.onload = function() {
                    var json;

                    if (xhr.status < 200 || xhr.status >= 300) {
                        // bugfix: 画像アップロード・ハンドラに存在する引数 failure コールバック変数はないため、コンソールのエラー出力に修正
                        console.error('HTTP Error: ' + xhr.status);
                        return;
                    }
                    //alert(xhr.status);

                    json = JSON.parse(xhr.responseText);

                    //alert(xhr.responseText);
                    //alert(json.return_text);

                    //if (!json || typeof json.location != 'string') {
                    //	failure('Invalid JSON: ' + xhr.responseText);
                    //	return;
                    //}

                    //success(json.location);

                    // サーバから戻ってきたHTMLをエディタ画面に挿入する。
                    // console.log(json);
                    // console.log(json.link_texts);
                    for (var i in json.link_texts) {
                        // change: tinymce5対応
                        // tinymce.EditorManager.activeEditor.insertContent(json.link_texts[i]);
                        editor.insertContent(json.link_texts[i]);
                    }

                    // bugfix: １画面に複数ウィジウィグがあるプラグイン(blog, database)のために、ここでクリア
                    document.getElementById('cc-file-upload-file1-' + frame_id).value = '';
                    document.getElementById('cc-file-upload-file2-' + frame_id).value = '';
                    document.getElementById('cc-file-upload-file3-' + frame_id).value = '';
                    document.getElementById('cc-file-upload-file4-' + frame_id).value = '';
                    document.getElementById('cc-file-upload-file5-' + frame_id).value = '';

                    // 閉じる
                    api.close();
                };

                //alert(e.data.file1);
                //console.log(jQuery('.mce-cc-file-upload'));
                //return;

                var tokens = document.getElementsByName("csrf-token");
                //alert(tokens[0].content);
                var page_id = document.getElementsByName("_page_id");

                formData = new FormData();
                //formData.append('plugin_name', e.data.plugin_name);
                formData.append('_token', tokens[0].content);
                formData.append('page_id', page_id[0].content);
                formData.append('plugin_name', editor.options.get('cc_config').plugin_name);

                // change: tinymce5対応
                // TinyMCE が出力するinputタグにnameがないため、classで選択。classesでの指定はcc-file-uploadだが、TinyMCE が mce- を付けて出力する。
                // formData.append('file1', jQuery('.mce-cc-file-upload')[0].files[0]);
                // formData.append('file2', jQuery('.mce-cc-file-upload')[1].files[0]);
                // formData.append('file3', jQuery('.mce-cc-file-upload')[2].files[0]);
                // formData.append('file4', jQuery('.mce-cc-file-upload')[3].files[0]);
                // formData.append('file5', jQuery('.mce-cc-file-upload')[4].files[0]);

                // type で指定する方法。画面上に他にinput type=file がいるとおかしくなると思うので、classでのセレクタにした。
                //formData.append('file1', jQuery('input[type=file]')[0].files[0]);

                // tinymce5で input type fileが無くなったため、wysiwyg.blade.phpに用意した非表示のinput type fileを使って送信
                // var frame_id = document.getElementsByName("frame_id")[0].value;
                var frame_id = editor.options.get('cc_config').frame_id;

                // console.log(frame_id[0].value);
                formData.append('file1', document.getElementById('cc-file-upload-file1-' + frame_id).files[0]);
                formData.append('file2', document.getElementById('cc-file-upload-file2-' + frame_id).files[0]);
                formData.append('file3', document.getElementById('cc-file-upload-file3-' + frame_id).files[0]);
                formData.append('file4', document.getElementById('cc-file-upload-file4-' + frame_id).files[0]);
                formData.append('file5', document.getElementById('cc-file-upload-file5-' + frame_id).files[0]);

                xhr.send(formData);

                //if (e.data.inline_block === "inline") { var inline_block = ["&", ";"] } else { var inline_block = ["#", ""] }
                //if (e.data.inline_text) { var inline_text = e.data.inline_text; inline_text = inline_text.replace(/\r?\n/g, "<br>"); } else { var inline_text = ""}
                //editor.insertContent( '<span class="plugin" contenteditable="false" style="cursor: default;" data-mce-style="cursor: default;">' + 'テスト' + '(' + e.data.plugin_option + ')' + inline_text + '</span>');
            }
        });
    } // function pluginWin()
});
