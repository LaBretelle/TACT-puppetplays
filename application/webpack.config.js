var Encore = require('@symfony/webpack-encore');
// enable tinymce skins
var CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())

    // allow legacy applications to use $/jQuery as a global variable
    /*.autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
        Popper: 'popper.js',
        _: 'underscore',
    })*/

    // define the assets of the project
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/home', './assets/js/home.js')
    .addEntry('js/create-project', './assets/js/create-project.js')


    .addPlugin(new CopyWebpackPlugin([
        // Copy the skins from tinymce to the build/js/skins directory
        { from: 'node_modules/tinymce/skins', to: 'js/skins' },
    ]))
    .addStyleEntry('css/app', './assets/css/global.scss')
    .addStyleEntry('css/account', './assets/css/account.scss')

    // uncomment if you use Sass/SCSS files
    .enableSassLoader(function(sassOptions) {}, {
         resolveUrlLoader: false
     })

     // will prefix css properties according to the supported browser set in postcss.config.js
     .enablePostCssLoader()

    // for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
