// --- codemirror 6
import { EditorView, placeholder } from "@codemirror/view";
window.EditorView = EditorView;
window.placeholder = placeholder;   // プレースホルダー有効化
import { basicSetup } from "codemirror"
window.basicSetup = basicSetup;
// 言語モード
// ※ 対応言語はgithubのcodemirrorでlang-xxxを確認 https://github.com/codemirror?q=lang-&type=all&language=&sort=
import { javascript } from "@codemirror/lang-javascript"
window.javascript = javascript;
import { css } from "@codemirror/lang-css"
window.css = css;
import { java } from "@codemirror/lang-java"
window.java = java;
import { php } from "@codemirror/lang-php"
window.php = php;
