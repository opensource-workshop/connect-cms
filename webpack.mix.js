const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js').vue()
    .sass('resources/sass/app.scss', 'public/css');

// TinyMCE 5
//   https://www.tiny.cloud/docs/tinymce/5/bundling-skins/
//   tinymce.jsを含むapp.jsから相対パスでスキンのcssが参照される
mix.copy([
    'node_modules/tinymce/skins/ui/oxide/skin.min.css',
    'node_modules/tinymce/skins/ui/oxide/content.min.css'
], 'public/js/skins/ui/oxide');

// fortawesomeのディレクトリインストール対応で、相対パスを指定
mix.setResourceRoot('../');

if (mix.inProduction()) {
    mix.version();
}
