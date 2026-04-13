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
    .js('resources/js/wysiwyg.js', 'public/js')
    .js('resources/js/codemirror.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/wysiwyg-editor.scss', 'public/css');

// fortawesomeのディレクトリインストール対応で、相対パスを指定
mix.setResourceRoot('../');

if (mix.inProduction()) {
    mix.version();
}
