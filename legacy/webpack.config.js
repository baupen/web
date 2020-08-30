const Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
  .setOutputPath('public/dist/')

  // the public path used by the web server to access the previous directory
  .setPublicPath('/dist')

  // will create public/build/app.js and public/build/app.css
  .addEntry('app', './assets/js/app.js')
  .copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[ext]'
  })

  // allow sass/scss files to be processed
  .enableSassLoader()

  // auto prefix css for more browser support
  .enablePostCssLoader()

  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3,
    includeNodeModules: ['epic-spinners', 'moment']
  })

  // allow legacy applications to use $/jQuery as a global variable
  .autoProvidejQuery()

  /*
  .enableTypeScriptLoader()
  .enableForkedTypeScriptTypesChecking()
  */

  // enable vue.js loader
  .enableVueLoader(() => {}, { runtimeCompilerBuild: false })

  // allow debugging of minified assets
  .enableSourceMaps(!Encore.isProduction())

  // empty the outputPath dir before each build
  .cleanupOutputBeforeBuild()

  // create hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // disable optimization with runtime chunks
  .disableSingleRuntimeChunk()
;

// export the final configuration
module.exports = Encore.getWebpackConfig()
