const webpack = require('webpack');
const path = require('path');
const CopyPlugin = require("copy-webpack-plugin");
const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
    mode: 'production',
    resolve: {
        alias: {
            'vue': "vue/dist/vue.esm-bundler.js",
            'vue-i18n': "vue-i18n/dist/vue-i18n.esm-bundler.js",
            'dayjs': "dayjs/dayjs.min.js",
            'Chart': "chart.js/dist/chart.esm.js",
            'Basil': "basil.js/build/basil.min.js"
        }
    },
    entry: {
        'app': {
            import: './src-frontend/scripts/app.js',
            dependOn: 'vendor'
        },
        'vendor': ['vue', 'vue-router', 'vue-i18n', 'axios', 'mitt', 'dayjs', 'Basil', 'Chart', 'audiomotion-analyzer']
    },
    output: {
        path: path.resolve(__dirname, 'public/scripts/'),
        publicPath: '/scripts/',
        filename: '[name]-bundle-[contenthash].min.js',
        clean: true
    },
    plugins: [
        // to remove warn in browser console: runtime-core.esm-bundler.js:3607 Feature flags __VUE_OPTIONS_API__, __VUE_PROD_DEVTOOLS__ are not explicitly defined...
        new webpack.DefinePlugin({ __VUE_OPTIONS_API__: true, __VUE_PROD_DEVTOOLS__: true }),
        new CopyPlugin({
            patterns: [
                { from: path.resolve(__dirname, 'src-frontend/icons'), to: path.resolve(__dirname, 'public/icons') },
                { from: path.resolve(__dirname, 'src-frontend/images'), to: path.resolve(__dirname, 'public/images') },
                { from: path.resolve(__dirname, 'src-frontend/styles'), to: path.resolve(__dirname, 'public/styles') },
            ],
        }),
        new HtmlWebpackPlugin({
            template: path.resolve(__dirname, 'templates/index-webpack.html.twig'),
            filename: path.resolve(__dirname, 'templates/index.html.twig'),
            hash: false
        })
    ]
};