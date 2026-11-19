const path = require('path'),
    TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    optimization: {
        minimize: false,
        minimizer: [new TerserPlugin()],
    },
    module: {
    },
    stats: {
        children: true,
        warnings: false,
    },
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            'css@': path.resolve('resources/css'),
            'v@': path.resolve('resources/js/vue'),
            'store@': path.resolve('resources/js/vue/store'),
            'm@': path.resolve('resources/js/vue/mixins'),
            'c@': path.resolve('resources/js/vue/components'),
        },
    },
};
