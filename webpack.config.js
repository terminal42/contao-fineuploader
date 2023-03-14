const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42fineuploader')
    .setManifestKeyPrefix('')

    .addEntry('backend', './assets/backend.js')
    .addEntry('frontend', './assets/frontend.js')

    .copyFiles({
        from: './node_modules/fine-uploader/fine-uploader',
        to: 'fine-uploader/[path][name].[ext]'
    })

    .copyFiles({
        from: './node_modules/sortablejs',
        pattern: /Sortable\.min\.js$/,
        to: 'sortable.[contenthash:8].min.js',
    })

    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps()
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
