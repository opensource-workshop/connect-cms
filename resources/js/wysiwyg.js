// --- TinyMCE
// 「Example src/editor.js」よりコピーして編集: https://www.tiny.cloud/docs/tinymce/latest/vite-es6-npm/#procedures
/* Import TinyMCE */
import tinymce from 'tinymce';
window.tinymce = tinymce;

/* Default icons are required. After that, import custom icons if applicable */
import 'tinymce/icons/default';

/* Required TinyMCE components */
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

/* Import a skin (can be a custom skin instead of the default) */
import 'tinymce/skins/ui/oxide/skin.js';

/* Import plugins */
// delete: imagetools はTinyMCE 6.xのオープンソース版から削除されてPremium版に移りました
// delete: template はTinyMCE 7.xのオープンソース版から削除されてPremium版に移りました
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/code';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/media';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/preview';

/* Import plugins(Connect-CMS Custom)
   コピー元Path：   node_modules/tinymce/plugins/image/plugin.js */
// import 'tinymce/plugins/image';
import './tinymce/plugins/image/plugin.js';

/* Import plugins(Connect-CMS Only)
   Path: resources/js/tinymce/plugins/... */
import './tinymce/plugins/file/plugin.js';
import './tinymce/plugins/translate/plugin.js';
import './tinymce/plugins/pdf/plugin.js';
import './tinymce/plugins/face/plugin.js';
import './tinymce/plugins/cc_template/plugin.js';
import './tinymce/plugins/cc_image_editor/plugin.js';

/* content UI CSS is required */
import 'tinymce/skins/ui/oxide/content.js';

/* The default content CSS can be changed or replaced with appropriate CSS for the editor content. */
// delete: contentCssは使用しない
//         参考Path: node_modules/tinymce/skins/content/default/content.js
// import 'tinymce/skins/content/default/content.js';

/* ダウンロードした日本語 https://www.tiny.cloud/get-tiny/language-packages/
   Path: resources/js/tinymce/langs/ja.js */
import './tinymce/langs/ja.js';

// tui-image-editor本体とCSS
import ImageEditor from 'tui-image-editor';
window.ImageEditor = ImageEditor;
import 'tui-image-editor/dist/tui-image-editor.css';
import 'tui-color-picker/dist/tui-color-picker.css';
