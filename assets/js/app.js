import '../css/app.scss'
import './vuejs'

import { dom } from '@fortawesome/fontawesome-svg-core'

const $ = require('jquery')
window.$ = $
require('bootstrap')
require('typeface-open-sans')

// register some basic usability functionality
$(document)
  .ready(() => {
    // give instant feedback on form submission
    $('form')
      .on('submit', () => {
        const $form = $(this)
        const $buttons = $('.btn', $form)
        if (!$buttons.hasClass('no-disable')) {
          $buttons.addClass('disabled')
        }
      })

    $('[data-toggle="popover"]')
      .popover()

    $('[data-toggle="tooltip"]')
      .tooltip()

    // force reload on user browser button navigation
    $(window)
      .on('popstate', () => {
        window.location.reload(true)
      })

    dom.watch()
  })
