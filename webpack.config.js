const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42fineuploader')
    .setManifestKeyPrefix('')

    .addEntry('backend', './assets/backend.js')
    .addEntry('frontend', './assets/frontend.js')
    .addEntry('sortable', './node_modules/sortablejs/Sortable.js')

    .copyFiles({
        from: './node_modules/fine-uploader/fine-uploader',
        to: 'fine-uploader/[path][name].[ext]'
    })

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    // .splitEntryChunks()

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

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
