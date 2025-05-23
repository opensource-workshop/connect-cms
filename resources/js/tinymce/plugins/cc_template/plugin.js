// resources/js/tinymce/plugins/cc_template/plugin.js

tinymce.PluginManager.add('cc_template', function(editor, url) {
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
            <div style="border: 1px solid; padding: 8px; margin-top: 4px; background-color: #f9f9f9;">${template.content || ''}</div>
          </div>
        `;
      }
    }

    // ウィジウィグエディタへカスタムプラグインのボタンを登録する
    editor.ui.registry.addButton('cc_template', {
      // see) https://www.tiny.cloud/docs/tinymce/latest/editor-icon-identifiers/
      icon: "template",
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
