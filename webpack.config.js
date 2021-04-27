const path = require('path');
const ReplaceHashInFileWebpackPlugin = require('replace-hash-in-file-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: './public/scripts/app.js',
    output: {
        filename: 'app-bundle.min.js',
        path: path.resolve(__dirname, 'public/scripts/'),
    },
    plugins: [
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