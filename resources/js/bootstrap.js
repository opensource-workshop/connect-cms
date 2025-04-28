
window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });


// --- Tempus Dominus Date/Time Picker
window.tempusDominus = require('@eonasdan/tempus-dominus');
// 下記でapp.jsに含めると、日付入力時に1度画面上部に飛ばされる動作をするため、含めない
// window.Popper = require('@popperjs/core');

// --- Default SortableJS
window.Sortable = require('sortablejs').default;

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

// --- Vue.js 3
import { createApp } from 'vue';
window.createApp = createApp;

// --- dayjs(日付フォーマット)
let dayjs = require('dayjs');
let utc = require("dayjs/plugin/utc");
let timezone = require("dayjs/plugin/timezone");
dayjs.extend(utc);
dayjs.extend(timezone);
window.dayjs = dayjs;
