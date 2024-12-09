const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore('assets')
    .setOutputPath('public/')
    .setPublicPath('/bundles/terminal42fineuploader')

    .copyFiles({
        from: './node_modules/fine-uploader/fine-uploader',
        to: 'fine-uploader/[path][name].[ext]'
    })

    .copyFiles({
        from: './node_modules/sortablejs',
        pattern: /Sortable\.min\.js$/,
        to: 'sortable.[contenthash:8].min.js',
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .getWebpackConfig()
;
