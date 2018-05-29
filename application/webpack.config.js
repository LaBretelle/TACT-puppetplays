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

  // define the assets of the project
  .addEntry('js/app', './assets/js/app.js')
  .addEntry('js/home', './assets/js/home.js')
  .addEntry('js/create-project', './assets/js/create-project.js')
  .addEntry('js/user-status-form', './assets/js/user-status-form.js' )
  .addEntry('js/user-list', './assets/js/admin/user-list.js' )
  .addEntry('js/app-routing', './assets/js/modules/app-routing.js' )
  .addEntry('js/account', './assets/js/account.js' )
  .addEntry('js/project-media', './assets/js/project-media.js' )

  .addPlugin(new CopyWebpackPlugin([
    // Copy the skins from tinymce to the build/js/skins directory
    {
      from: 'node_modules/tinymce/skins',
      to: 'js/skins'
    },
  ]))

  .addStyleEntry('css/app', './assets/css/global.scss')
  .addStyleEntry('css/account', './assets/css/account.scss')
  .addStyleEntry('css/create-project', './assets/css/create-project.scss')
  .addStyleEntry('css/project-media', './assets/css/project-media.scss')
  .addStyleEntry('css/user', './assets/css/admin/user.scss')

  .enableSassLoader(function(sassOptions) {}, {
    resolveUrlLoader: false
  })

  // will prefix css properties according to the supported browser set in postcss.config.js
  .enablePostCssLoader()

  .configureBabel(function(babelConfig) {
        babelConfig.presets.push('es2017');
  })

  .createSharedEntry('vendor', [
        'jquery',
        'bootstrap',
        '@fortawesome/fontawesome',
        '@fortawesome/fontawesome-free-solid',
        '@fortawesome/fontawesome-free-brands',
        '@fortawesome/fontawesome-free-webfonts'
  ])


  // for legacy applications that require $/jQuery as a global variable
  .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
