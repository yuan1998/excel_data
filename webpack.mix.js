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

mix.js('resources/js/app.js', 'public/js')
   .less('resources/less/app.less', 'public/css')
   .webpackConfig({
       resolve  : {
           extensions: [ '.js','.vue' ],
           alias     : {
               '@'  : __dirname + '/resources/js'
           }
       },
       externals:{
           'jquery':'jQuery',
           'sweetalert2':'Swal',
           'vue': 'Vue'
       }
   });
