const path = require('path');
const CopyPlugin = require("copy-webpack-plugin");
const ReplaceHashInFileWebpackPlugin = require('replace-hash-in-file-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: './src-frontend/scripts/app.js',
    output: {
        filename: 'app-bundle.min.js',
        path: path.resolve(__dirname, 'public/scripts/'),
        publicPath: '/scripts/'
    },
    plugins: [
        new CopyPlugin({
            patterns: [
                { from: path.resolve(__dirname, 'src-frontend/'), to: path.resolve(__dirname, 'public/') }
            ],
        }),
        new ReplaceHashInFileWebpackPlugin(
            [
                {
                    dir: 'templates',
                    files: ['index.html.twig'],
                    rules: [
                        {
                            search: /<script type="module" src="scripts\/app-bundle.min.js\?*[a-zA-Z0-9]*"><\/script>/,
                            replace: '<script type="module" src="scripts/app-bundle.min.js?[hash]"></script>'
                        }
                    ]
                }
            ]
        )
    ]
};