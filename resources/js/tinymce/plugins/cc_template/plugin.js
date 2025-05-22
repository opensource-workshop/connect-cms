// resources/js/tinymce/plugins/cc_template/plugin.js

tinymce.PluginManager.add('cc_template', function(editor, url) {
    // アイコン定義 see) https://fontawesome.com/search?q=stamp&o=r ※svgコードそのままコピペでは表示されない為、widthとheightを指定
    editor.ui.registry.addIcon(
      'clone', 
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M312 201.8c0-17.4 9.2-33.2 19.9-47C344.5 138.5 352 118.1 352 96c0-53-43-96-96-96s-96 43-96 96c0 22.1 7.5 42.5 20.1 58.8c10.7 13.8 19.9 29.6 19.9 47c0 29.9-24.3 54.2-54.2 54.2L112 256C50.1 256 0 306.1 0 368c0 20.9 13.4 38.7 32 45.3L32 464c0 26.5 21.5 48 48 48l352 0c26.5 0 48-21.5 48-48l0-50.7c18.6-6.6 32-24.4 32-45.3c0-61.9-50.1-112-112-112l-33.8 0c-29.9 0-54.2-24.3-54.2-54.2zM416 416l0 32L96 448l0-32 320 0z"/></svg>'
    );

    // （共通処理）プレビュー表示を更新
    function updateTemplatePreview(api, templates) {
      const selected_idx = api.getData().template_select;
      const idx = parseInt(selected_idx, 10);

      // 空白選択時は何も表示しない
      if (selected_idx === '' || isNaN(idx)) {
        document.getElementById('template-preview').innerHTML = '';
        return;
      }

      // プレビュー要素に挿入
      const preview = document.getElementById('template-preview');
      if (preview) {
        const template = templates[selected_idx] || {};
        preview.innerHTML = `
          <div>
            <div>${template.description || ''}</div>
            <div>${template.content || ''}</div>
          </div>
        `;
      }
    }

    // ウィジウィグエディタへカスタムプラグインのボタンを登録する
    editor.ui.registry.addButton('cc_template', {
      icon: "clone",
      tooltip: 'テンプレートの挿入',
      onAction: function () {
        // テンプレートの取得
        const templates = (window.cc_templates && window.cc_templates.templates) || [];

        if (!Array.isArray(templates) || templates.length === 0) {
          editor.windowManager.alert('テンプレートが見つかりません。');
          return;
        }

        // ダイアログ画面定義＆画面アクション定義
        editor.windowManager.open({
          title: 'テンプレートの挿入',
          body: {
            type: 'panel',
            items: [
              {
                type: 'selectbox',
                name: 'template_select',
                label: 'テンプレート',
                items: [{ text: '選択してください。', value: '' }].concat(
                  templates.map((tpl, idx) => ({
                    text: tpl.title,
                    value: String(idx)
                  }))
                )
              },
              {
                type: 'htmlpanel',
                name: 'preview',
                html: '<div id="template-preview"></div>'
              }
            ]
          },
          buttons: [
            { type: 'cancel', text: 'キャンセル' },
            { type: 'submit', text: '挿入', primary: true }
          ],
          initialData: {
            template_select: ''
          },
          // セレクトボックス切り替え時の処理
          onChange(api) {
            updateTemplatePreview(api, templates);
          },
          // セレクトボックスで選択したcontentを挿入
          onSubmit(api) {
            const idx = parseInt(api.getData().template_select);
            editor.insertContent(templates[idx]?.content || '');
            api.close();
          }
        });
      }
    });
});
