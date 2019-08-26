const Encore = require('@symfony/webpack-encore')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const path = require('path')

Encore
  // the project directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // the public path used by the web server to access the previous directory
  .setPublicPath('/build')
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())

  // define the assets of the project
  .addEntry('js/app', './assets/js/app.js')
  .addEntry('js/create-project', './assets/js/create-project.js')
  .addEntry('js/user-status-form', './assets/js/user-status-form.js')
  .addEntry('js/user-list', './assets/js/admin/user-list.js')
  .addEntry('js/account', './assets/js/account.js')
  .addEntry('js/project-media', './assets/js/project-media.js')
  .addEntry('js/submit-disable', './assets/js/submit-disable.js')
  .addEntry('js/transcription', './assets/js/transcription.js')
  .addEntry('js/platform', './assets/js/platform.js')
  .addEntry('js/openseadragon', './node_modules/openseadragon/build/openseadragon/openseadragon.min.js')
  .addEntry('js/file-input', './node_modules/bs-custom-file-input/dist/bs-custom-file-input.min.js')

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
  .addStyleEntry('css/toastr', './node_modules/toastr/build/toastr.min.css')
  .addStyleEntry('css/intro', './node_modules/intro.js/minified/introjs.min.css')

  .enableSassLoader(function () {}, {
    resolveUrlLoader: false
  })

  // will prefix css properties according to the supported browser set in postcss.config.js
  .enablePostCssLoader()

  .configureBabel(function (babelConfig) {
    babelConfig.presets.push('es2017')
  })

  .createSharedEntry('vendor', [
    'jquery',
    'bootstrap',
    '@fortawesome/fontawesome-free/js/all'
  ])

  // for legacy applications that require $/jQuery as a global variable
  .autoProvidejQuery()
  .autoProvideVariables({
    'Routing': 'router',
    'Toastr': 'Toastr',
    'Translator': 'Translator',
    'TinyEditor': 'TinyEditor',
    'TeiEditor': 'TeiEditor'
  })
  .enableVersioning()


// get "REAL" webpack object
const config = Encore.getWebpackConfig()
config.resolve.alias = {
  'router': path.resolve(__dirname, 'modules/router.js'),
  'Toastr': path.resolve(__dirname, 'modules/toastr.js'),
  'Translator': path.resolve(__dirname, 'modules/translator.js'),
  'TinyEditor': path.resolve(__dirname, 'modules/tiny-editor.js'),
  'TeiEditor': path.resolve(__dirname, 'modules/tei-editor.js'),
}

// https://stackoverflow.com/questions/44439909/confusion-over-various-webpack-shimming-approaches
config.module = Object.assign(config.module, {
  loaders: [{
    test: require.resolve('tinymce/tinymce'),
    loaders: [
      'imports?this=>window',
      'exports?tinymce'
    ]
  },
  {
    test: /tinymce\/(themes|plugins)\//,
    loaders: [
      'imports?this=>window'
    ]
  }
  ]
})

module.exports = config
