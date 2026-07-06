const mix = require('laravel-mix');

mix.disableNotifications();
mix.autoload({
        'jquery': ['jQuery', '$'],
    })
    .webpackConfig(require('./webpack.config'))
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts')
    .copy('node_modules/leaflet/dist/images', 'public/css/images')
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .options({processCssUrls: false})
    .vue({version: 3})
;
