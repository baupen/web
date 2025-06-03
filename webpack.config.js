var Encore = require('@symfony/webpack-encore')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  // only needed for CDN's or sub-directory deploy
  // .setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Add 1 entry for each "page" of your app
   * (including one that's included on every page - e.g. "app")
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addStyleEntry('email', './assets/css/email.scss')
  .addEntry('app', './assets/js/app.js')
  .copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[ext]'
  })
  .copyFiles({
    from: './assets/videos',
    to: 'videos/[path][name].[ext]'
  })

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // auto prefix css for more browser support
  .enablePostCssLoader()

  // enables @babel/preset-env polyfills
  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3,
    includeNodeModules: ['moment']
  })

  // enables Sass/SCSS support
  .enableSassLoader(options => {
    options.implementation = require('sass')
    options.sassOptions.quietDeps = true
    options.sassOptions.silenceDeprecations = ['legacy-js-api',  'import', 'slash-div', 'global-builtin']
  })
  .enableVueLoader(() => {}, { runtimeCompilerBuild: false })
  // .enableVueLoader()
  .autoProvidejQuery()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  .enableIntegrityHashes(Encore.isProduction())

  .configureDevServerOptions(options => {
    // hotfix for webpack-dev-server 4.0.0rc0
    // @see: https://github.com/symfony/webpack-encore/issues/951#issuecomment-840719271
    delete options.client

    // options.firewall = false
    options.devMiddleware = {
      writeToDisk: true
    }
  })

  .configureDefinePlugin(options => {
    options.__VUE_I18N_LEGACY_API__ = true
    options.__VUE_I18N_FULL_INSTALL__ = true
    options.__INTLIFY_PROD_DEVTOOLS__ = false

    options.__VUE_OPTIONS_API__ = true
    options.__VUE_PROD_DEVTOOLS__ = false
    options.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false
  })

const webpackConfig = Encore.getWebpackConfig()

if (Encore.isProduction()) {
  // avoid global ship for CSP compliance
  // see https://github.com/webpack/webpack/issues/6461#issuecomment-36681342
  webpackConfig.node = {
    global: false,
    __filename: false,
    __dirname: false,
  }

  // use runtime build of vue-i18n for CSP compliance
  // see https://vue-i18n.intlify.dev/ja/guide/advanced/optimization.html
  webpackConfig.resolve.alias = Object.assign(webpackConfig.resolve.alias, {
    "vue-i18n": "vue-i18n/dist/vue-i18n.runtime.esm-bundler.js"
  })
}

module.exports = webpackConfig

