import { locale_ja_JP } from './locale_ja_JP.js';
tinymce.PluginManager.add('cc_image_editor', (editor, url) => {
    // Toast UI Editor のインスタンスを保持する変数
    let imageEditorInstance = null;
    // Toast UI Editor を表示するコンテナ要素
    let tuiContainer = null;

    const openToastUIEditorForSelectedImage = () => {
        const selectedNode = editor.selection.getNode();
        if (!(selectedNode && selectedNode.nodeName === 'IMG')) {
            console.error('No image selected or selected node is not an image.');
            return;
        }

        const imageUrl = selectedNode.getAttribute('src');
        const imageFileName = selectedNode.getAttribute('alt') || 'edited_image.png'; // ファイル名に拡張子を含めると良いでしょう

        // Toast UI Editor を表示するためのコンテナを準備
        const editorContainerId = 'tui-image-editor-container-modal';
        if (!tuiContainer) {
            tuiContainer = document.createElement('div');
            tuiContainer.id = editorContainerId;
            // モーダル風スタイル
            Object.assign(tuiContainer.style, {
                position: 'fixed',
                top: '0',
                left: '0',
                width: '100%',
                height: '100%',
                backgroundColor: 'rgba(0,0,0,0.85)',
                zIndex: '20000', // TinyMCEのUIより手前に
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
            });
            document.body.appendChild(tuiContainer);
        } else {
            tuiContainer.innerHTML = ''; // 既存のコンテンツをクリア
            tuiContainer.style.display = 'flex';
        }

        const tuiEditorDivId = 'tui-image-editor-instance-div';
        const tuiEditorDiv = document.createElement('div');
        tuiEditorDiv.id = tuiEditorDivId;
        Object.assign(tuiEditorDiv.style, {
            width: '90vw', // ビューポート幅の90%
            maxWidth: '1200px', // 最大幅
            height: '85vh', // ビューポート高さの85%
            maxHeight: '800px', // 最大高さ
            backgroundColor: '#1e1e1e', // Toast UI Editor のデフォルト背景色に合わせる
            borderRadius: '8px',
            overflow: 'hidden', // Toast UI Editor がはみ出ないように
            display: 'flex',
            flexDirection: 'column' // コントロールボタンをエディタの下に配置するため
        });
        tuiContainer.appendChild(tuiEditorDiv);

        const editorActualContainer = document.createElement('div');
        editorActualContainer.id = 'cc-tui-actual-editor';
        editorActualContainer.style.width = '100%';
        editorActualContainer.style.height = 'calc(100% - 50px)'; // コントロールボタンの高さを引く
        tuiEditorDiv.appendChild(editorActualContainer);


        // 古いインスタンスが残っていれば破棄 (念のため)
        if (imageEditorInstance) {
            try {
                imageEditorInstance.destroy();
            } catch (e) {
                console.warn("Error destroying previous TUI editor instance:", e);
            }
            imageEditorInstance = null;
        }

        imageEditorInstance = new ImageEditor(`#${editorActualContainer.id}`, {
            includeUI: {
                loadImage: {
                    path: imageUrl,
                    name: imageFileName,
                },
                uiSize: {
                    width: '100%',
                    height: '100%',
                },
                locale: locale_ja_JP,
            },
            usageStatistics: false,
        });

        // コントロールボタン（適用、キャンセル）のコンテナ
        const controlsDiv = document.createElement('div');
        controlsDiv.id = 'tui-editor-controls';
        Object.assign(controlsDiv.style, {
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            padding: '10px',
            backgroundColor: '#151515', // 少し暗い背景
            height: '50px', // コントロールの高さを確保
            boxSizing: 'border-box'
        });

        // 適用ボタン
        const applyButton = document.createElement('button');
        applyButton.innerText = '適用';
        applyButton.classList.add('btn', 'btn-primary', 'mr-2');
        applyButton.onclick = async () => { // async 関数にする
            if (!imageEditorInstance) return;

            const editedImageDataURL = imageEditorInstance.toDataURL(); // Base64データ

            // Base64データをBlobに変換
            let imageBlob;
            try {
                const response = await fetch(editedImageDataURL);
                imageBlob = await response.blob();
            } catch (error) {
                console.error('Error converting Base64 to Blob:', error);
                alert('画像の処理に失敗しました。');
                return;
            }

            // FormDataを作成して画像データを追加
            const formData = new FormData();
            const fileName = selectedNode.getAttribute('alt') || `edited_image_${Date.now()}.png`;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const pageId = document.querySelector('meta[name="_page_id"]')?.getAttribute('content')
            formData.append('image', imageBlob, fileName);
            formData.append('_token', csrfToken);
            formData.append('page_id', pageId);
            formData.append('plugin_name', editor.options.get('cc_config').plugin_name);

            // 画像をサーバーにアップロード
            try {
                const uploadResponse = await fetch('/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                if (!uploadResponse.ok) {
                    let errorMessage = `画像のアップロードに失敗しました: ${uploadResponse.statusText}`;
                    try {
                        const errorData = await uploadResponse.json();
                        errorMessage = errorData.message || errorData.error || errorMessage;
                    } catch (e) {
                        console.warn('Could not parse error response as JSON.');
                    }
                    throw new Error(errorMessage);
                }

                const responseData = await uploadResponse.json();
                const newImageUrl = responseData.location;

                if (!newImageUrl) {
                    throw new Error('サーバーからのレスポンスに画像URLが含まれていません。');
                }

                if (selectedNode) {
                    editor.undoManager.transact(() => {
                        editor.dom.setAttrib(selectedNode, 'src', newImageUrl);
                        editor.dispatch('Change');
                    });
                }
                closeToastUIEditor();
            } catch (error) {
                console.error('Upload failed:', error);
                alert(error.message || '画像のアップロード中にエラーが発生しました。');
            }
        };

        // キャンセルボタン
        const cancelButton = document.createElement('button');
        cancelButton.innerText = 'キャンセル';
        cancelButton.classList.add('btn', 'btn-secondary', 'mr-2');
        cancelButton.onclick = () => {
            closeToastUIEditor();
        };

        controlsDiv.appendChild(cancelButton);
        controlsDiv.appendChild(applyButton);
        tuiEditorDiv.appendChild(controlsDiv);
    };

    const closeToastUIEditor = () => {
        if (imageEditorInstance) {
            try {
                imageEditorInstance.destroy();
            } catch (e) {
                console.warn("Error destroying TUI editor instance on close:", e);
            }
            imageEditorInstance = null;
        }
        if (tuiContainer) {
            tuiContainer.style.display = 'none';
            tuiContainer.innerHTML = ''; // コンテナの中身を空にする
        }
    };

    // 画像が選択されたときに表示されるコンテキストツールバーを登録
    editor.ui.registry.addContextToolbar('customImageEditToolbar', {
        predicate: (node) => node.nodeName === 'IMG', // IMG要素の場合に表示
        items: 'customEditImageButton', // 表示するボタンのキー (下記で定義)
        position: 'node', // 要素の近くに表示 (node, selection, line)
        scope: 'node' // 'node' or 'editor'
    });

    // コンテキストツールバーに表示するボタンを登録
    editor.ui.registry.addButton('customEditImageButton', {
        icon: 'edit-image',
        tooltip: '画像を編集',
        onAction: openToastUIEditorForSelectedImage
    });

    // プラグインが破棄される時にリソースをクリーンアップ
    editor.on('remove', () => {
        closeToastUIEditor();
        if (tuiContainer && tuiContainer.parentNode) {
            tuiContainer.parentNode.removeChild(tuiContainer);
            tuiContainer = null;
        }
    });

    return {
        getMetadata: () => ({
            name: "Custom Image Edit (Context Toolbar & Undo)",
            url: "https://connect-cms.jp"
        })
    };
});

