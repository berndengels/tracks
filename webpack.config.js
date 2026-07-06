const path = require('path'),
    webpack = require('webpack'),
    supportedLocales = ['de'];

module.exports = {
    module: {
        rules: [
//            { test: /\.css$/, use: 'css-loader' },
        ]
    },
    stats: {
        children: true,
        warnings: false,
    },
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            'v@': path.resolve('resources/js/vue'),
            'pinia@': path.resolve('resources/js/vue/stores'),
            'm@': path.resolve('resources/js/vue/mixins'),
            'c@': path.resolve('resources/js/vue/components'),
/*
            '@fonts': path.resolve('public/fonts'),
            '@modules': path.resolve('node_modules'),
*/
        },
    },
};
