const path = require('path');
const CopyPlugin = require("copy-webpack-plugin");
const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
    mode: 'production',
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js"
        }
    },
    entry: {
        'app': {
            import: './src-frontend/scripts/app.js',
            dependOn: 'vendor'
        },
        'vendor': ['vue', 'vue-router', 'vue-i18n', 'axios']
    },
    output: {
        path: path.resolve(__dirname, 'public/scripts/'),
        publicPath: '/scripts/',
        filename: '[name]-bundle.min.js',
        //clean: true
    },
    plugins: [
        new CopyPlugin({
            patterns: [
                { from: path.resolve(__dirname, 'src-frontend/index.php'), to: path.resolve(__dirname, 'public/') },
                { from: path.resolve(__dirname, 'src-frontend/styles'), to: path.resolve(__dirname, 'public/styles') },
                { from: path.resolve(__dirname, 'src-frontend/images'), to: path.resolve(__dirname, 'public/images') },
                { from: path.resolve(__dirname, 'src-frontend/icons'), to: path.resolve(__dirname, 'public/icons') }
            ],
        }),
        new HtmlWebpackPlugin({
            template: path.resolve(__dirname, 'templates/index-webpack.html.twig'),
            filename: path.resolve(__dirname, 'templates/index.html.twig'),
            hash: true
        })
    ]
};