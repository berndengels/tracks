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
            'v@': path.resolve('resources/js/vue'),
            'pinia@': path.resolve('resources/js/vue/stores'),
            'm@': path.resolve('resources/js/vue/mixins'),
            'c@': path.resolve('resources/js/vue/components'),
        },
    },
};
