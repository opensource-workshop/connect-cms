tinymce.PluginManager.add('translate', function(editor, url) {
    // add: tinymce5対応
    // アイコン see) https://fontawesome.com/icons/language?f=classic&s=solid
    editor.ui.registry.addIcon('translate', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="24" height="24"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M0 128C0 92.7 28.7 64 64 64l192 0 48 0 16 0 256 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64l-256 0-16 0-48 0L64 448c-35.3 0-64-28.7-64-64L0 128zm320 0l0 256 256 0 0-256-256 0zM178.3 175.9c-3.2-7.2-10.4-11.9-18.3-11.9s-15.1 4.7-18.3 11.9l-64 144c-4.5 10.1 .1 21.9 10.2 26.4s21.9-.1 26.4-10.2l8.9-20.1 73.6 0 8.9 20.1c4.5 10.1 16.3 14.6 26.4 10.2s14.6-16.3 10.2-26.4l-64-144zM160 233.2L179 276l-38 0 19-42.8zM448 164c11 0 20 9 20 20l0 4 44 0 16 0c11 0 20 9 20 20s-9 20-20 20l-2 0-1.6 4.5c-8.9 24.4-22.4 46.6-39.6 65.4c.9 .6 1.8 1.1 2.7 1.6l18.9 11.3c9.5 5.7 12.5 18 6.9 27.4s-18 12.5-27.4 6.9l-18.9-11.3c-4.5-2.7-8.8-5.5-13.1-8.5c-10.6 7.5-21.9 14-34 19.4l-3.6 1.6c-10.1 4.5-21.9-.1-26.4-10.2s.1-21.9 10.2-26.4l3.6-1.6c6.4-2.9 12.6-6.1 18.5-9.8l-12.2-12.2c-7.8-7.8-7.8-20.5 0-28.3s20.5-7.8 28.3 0l14.6 14.6 .5 .5c12.4-13.1 22.5-28.3 29.8-45L448 228l-72 0c-11 0-20-9-20-20s9-20 20-20l52 0 0-4c0-11 9-20 20-20z"/></svg>');

    // ウィンドウに入力した値
    // var post_contents = '';

    // プラグインボタンの追加
    // change: tinymce5対応 see) https://www.tiny.cloud/docs/advanced/creating-a-plugin/
    // editor.addButton("translate", {
    //     icon: "plugin",
    // 	   image: "/js/tinymce/plugins/translate/translate.svg",
    editor.ui.registry.addButton("translate", {
        icon: "translate",
        tooltip: "翻訳",
        // change: tinymce5対応
        // onclick: pluginWin, // コールバック関数
        onAction: pluginWin,

        onPostRender: function() { // プラグイン要素選択時プラグインボタンアクティブ
            var _this = this;
            editor.on("NodeChange", function(e) {
                //var is_active = jQuery( editor.selection.getNode() ).hasClass("ref");
                var is_active = jQuery( editor.selection.getNode() ).hasClass("plugin");
                _this.active( is_active );
            })

            editor.on("DblClick", function(e) {
                //if ( e.target.className == "plugin" || e.target.className=="ref" ) {
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
            // title: "翻訳（英語に翻訳されます）",
            title: "翻訳",
            width: 600,
            height: 250,
            // change: tinymce5対応
            // body: [{
            // 	type: "textbox",
            // 	name: "inline_text",
            // 	multiline: true,
            // 	minHeight: 220,
            // 	classes : "cc-textarea",
            // 	value : editor.selection.getContent(),
            // 	label: ''
            // }],
            body: {
                type: 'panel',
                items: [
                    {
                        // see) https://www.tiny.cloud/docs/ui-components/dialogcomponents/#textarea
                        type: "textarea",
                        name: "inline_text",
                        label: '',
                        maximized: true // できるだけ多くのスペースを取るために幅を広げます
                    },
                    {
                        type: 'selectbox', // component type
                        name: 'target_language', // identifier
                        label: '翻訳後の言語',
                        size: 1, // number of visible values (optional)
                        items: [
                        { value: 'en', text: '英語' },
                        { value: 'pt', text: 'ポルトガル語' },
                        { value: 'vi', text: 'ベトナム語' },
                        { value: 'fr', text: 'フランス語' },
                        { value: 'de', text: 'ドイツ語' },
                        { value: 'es', text: 'スペイン語' },
                        { value: 'zh', text: '中国語 (簡体字)' },
                        { value: 'zh-TW', text: '中国語 (繁体字)' },
                        { value: 'ko', text: '韓国語' },
                        { value: 'tl', text: 'タガログ語' },
                        ]
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
                    text: '翻訳',
                    primary: true
                }
            ],
            initialData: {
                inline_text: editor.selection.getContent()
            },
            // OKボタンをクリックした際の挙動
            // change: tinymce5対応
            // onsubmit: function(e) {
            onSubmit: function(api) {
                //if (e.data.plugin_name === "") { // 入力値のバリデーション（簡易版）
                //	tinyMCE.activeEditor.windowManager.alert("プラグイン名が入力されていません。");
                //	return;
                //}

                // see) https://www.tiny.cloud/docs/ui-components/dialog/#dialogapimethods
                var data = api.getData();

                //alert("XMLHttpRequest");
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                //xhr.open('POST', '/debug/postTest.php');
                // xhr.open('POST', '/api/translate/post');
                xhr.open('POST', tinymce.activeEditor.getParam('document_base_url') + '/api/translate/post');

                xhr.onload = function() {
                    var json;

                    if (xhr.status < 200 || xhr.status >= 300) {
                        // bugfix: 画像アップロード・ハンドラに存在する引数 failure コールバック変数はないため、コンソールのエラー出力に修正
                        // failure('HTTP Error: ' + xhr.status);
                        console.error('HTTP Error: ' + xhr.status);
                        return;
                    }
                    //alert(xhr.status);

                    // console.log(xhr.responseText);
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
                    // console.log(json.return_texts);

                    for (var i in json.return_texts) {
                        // if (!post_contents) {
                        if (!data.inline_text) {
                            // change: tinymce5対応
                            // tinymce.EditorManager.activeEditor.insertContent('<p>' + json.return_texts[i] + '</p>');
                            editor.insertContent('<p>' + json.return_texts[i] + '</p>');
                        }
                        else {
                            // change: tinymce5対応
                            // tinymce.EditorManager.activeEditor.insertContent('<p>' + post_contents + '<br />' + json.return_texts[i] + '</p>');
                            // editor.insertContent('<p>' + post_contents + '<br />' + json.return_texts[i] + '</p>');
                            editor.insertContent('<p>' + data.inline_text + '<br />' + json.return_texts[i] + '</p>');
                        }
                    }

                    // 閉じる
                    api.close();
                };

                var tokens = document.getElementsByName("csrf-token");
                //alert(tokens[0].content);
                // console.log(api.getData());

                formData = new FormData();
                //formData.append('plugin_name', e.data.plugin_name);
                formData.append('_token', tokens[0].content);

                // TinyMCE が出力するinputタグにnameがないため、classで選択。
                // change: tinymce5対応
                // formData.append('inline_text', jQuery('.mce-cc-textarea').val());
                // formData.append('inline_text', jQuery('.tox-textarea')[0].value);
                formData.append('inline_text', data.inline_text);
                formData.append('target_language', data.target_language);

                // 入力された内容をグローバル変数に保持
                // change: tinymce5対応
                // post_contents = jQuery('.mce-cc-textarea').val();
                // post_contents = jQuery('.tox-textarea')[0].value;

                xhr.send(formData);
            }
        });

        // add: tinymce5対応
        // ダイアログのtextareaにvalueセット
        textarea = jQuery('.tox-textarea')[0];
        // textarea.value = editor.selection.getContent();
        textarea.setAttribute('style', 'min-height: 220px;');

        // console.log(editor.selection.getContent());

    } // function pluginWin()
});
