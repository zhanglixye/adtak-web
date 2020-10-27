const mix = require('laravel-mix');
const glob = require('glob');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// エントリーポイント, 出力先 の指定
glob.sync('resources/assets/js/app/**/*.js').map(function(file) {
    mix.js(file, 'public/js');
});

mix.sass('resources/assets/sass/app.scss', 'public/css')
    .sass('resources/assets/sass/client.scss', 'public/css');

// bizディレクトリ直下のscssファイルを全てコンパイル
glob.sync('resources/assets/sass/biz/*.scss').map(function(file) {
    mix.sass(file, 'public/css/biz');
});

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.styl$/,
                loader: ['style-loader', 'css-loader', 'stylus-loader']
            }
        ]
    },
    plugins: [
        new MomentLocalesPlugin({
            // can only be used, "en, ja, zh-cn"
            localesToKeep: ['ja', 'zh-cn'],
        }),
    ],
    optimization: {
        splitChunks: {
            chunks: 'all',
            minSize: 0,
            cacheGroups: {
                vendor: {
                    name: 'js/vendor',
                    test: /[\\/]node_modules[\\/]/,
                    priority: -10
                },
                default: false
            }
        }
    }
})

if (mix.inProduction()) {
    mix.version();
} else {
    require('laravel-mix-bundle-analyzer');
    mix.bundleAnalyzer({
        openAnalyzer: false,
        analyzerMode: 'static'
    });
}
