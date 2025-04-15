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

// fortawesomeのディレクトリインストール対応で、相対パスを指定
mix.setResourceRoot('../');

// @vue/compat（別名「移行ビルド」）は、設定可能な Vue 2 互換の動作を提供する、Vue 3 のビルドです。
mix.webpackConfig({
    resolve: {
        alias: {
            vue: '@vue/compat'
        }
    }
})

if (mix.inProduction()) {
    mix.version();
}
