const mix = require('laravel-mix');

mix.disableNotifications();
mix.autoload({
        'jquery': ['jQuery', '$'],
    })
    .webpackConfig(require('./webpack.config'))
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts')
    .copy('node_modules/leaflet/dist/images', 'public/images')
    .copy('node_modules/leaflet.markercluster/dist', 'public/leaflet.markercluster')
    .copy('node_modules/leaflet.measurecontrol/images', 'public/images')
    .copy('resources/assets/favicon', 'public')
    .css('resources/css/tracks.css', 'public/css')
    .js('resources/js/app.js', 'public/js')
    .js('resources/js/app-admin.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .options({processCssUrls: false})
    .vue({version: 3})
;
