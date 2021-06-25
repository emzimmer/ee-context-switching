
/**
 * The guts of the operation
 */
const path = require('path');

module.exports = {
    entry: './_src/js/index.js',
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'style-flow.js'
    },
    module: {
        rules: [
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    'style-loader',
                    'css-loader',
                    'postcss-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                loader: 'babel-loader',
                options: {
                    presets: [
                        '@babel/preset-env',
                        '@babel/preset-react'
                    ]
                }
            }
        ]
    }
}
