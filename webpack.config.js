const path = require('path'),
//    webpack = require('webpack'),
//    supportedLocales = ['de'],
    TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    optimization: {
        minimize: false,
        minimizer: [new TerserPlugin()],
    },
    module: {
//        rules: [{ test: /\.css$/, use: 'css-loader' }]
/*
        rules: [{
            test: /\.(js|jsx)$/,
            exclude: /node_modules/,
            use: {
                loader: "babel-loader",
                options: {
                    presets: ["@babel/preset-react", "@babel/preset-env"],
                },
            },
        }]
*/
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
