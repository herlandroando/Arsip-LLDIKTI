const mix = require('laravel-mix');
// require('laravel-mix-alias');
const path = require('path');


/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js').webpackConfig({
    resolve: {
        alias: {
            '@lang': path.resolve(__dirname,'resources/js/Lang'),
            '@shared': path.resolve(__dirname,'resources/js/Shared'),
            '@pages': path.resolve(__dirname,'resources/js/Pages'),
            '@rules': path.resolve(__dirname,'resources/js/Rules'),
        }
    }
})
    .vue()
    .postCss('resources/css/index.css', 'public/css/index.css', [
        //
    ]).
    postCss('resources/css/app.css', 'public/css/app.css', [])
    // .sass('resources/sass/app.scss','public/css/test.css')
    ;
//
