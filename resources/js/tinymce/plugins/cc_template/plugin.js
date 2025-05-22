// resources/js/tinymce/plugins/cc_template/plugin.js

tinymce.PluginManager.add('cc_template', function(editor, url) {
    // アイコン定義 see) https://fontawesome.com/icons/clone?f=classic&s=solid ※svgコードそのままコピペでは表示されない為、widthとheightを指定
    editor.ui.registry.addIcon(
      'clone', 
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M288 448L64 448l0-224 64 0 0-64-64 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l224 0c35.3 0 64-28.7 64-64l0-64-64 0 0 64zm-64-96l224 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L224 0c-35.3 0-64 28.7-64 64l0 224c0 35.3 28.7 64 64 64z"/></svg>'
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
