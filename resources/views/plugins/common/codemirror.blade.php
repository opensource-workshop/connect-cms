{{--
 * コード色装飾＆入力テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@php
    // CodeMirrorの設定
    $element_id = $element_id ?? null;
    $mode = $mode ?? 'javascript()';
    $height = $height ?? '300px';
@endphp

<style>
    .cm-editor {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        height: {{$height}};
    }
</style>
<script type="text/javascript">
    let view_{{$element_id}} = editorFromTextArea(document.getElementById("{{$element_id}}"))

    /**
     * codemirror 5 互換 function
     * @see https://codemirror.net/docs/migration/#codemirror.fromtextarea よりコピー
     */
    function editorFromTextArea(textarea) {
        let view = new EditorView({
            doc: textarea.value,
            extensions: [
                basicSetup,
                placeholder(textarea.placeholder),  // プレースホルダ
                EditorView.lineWrapping,            // 行を折り返す
                {{$mode}},                          // 言語モード
            ],
        })
        textarea.parentNode.insertBefore(view.dom, textarea)
        textarea.style.display = "none"
        if (textarea.form) textarea.form.addEventListener("submit", () => {
            textarea.value = view.state.doc.toString()
        })
        return view
    }
</script>
