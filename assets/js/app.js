import '../css/app.scss'
import './vuejs'

const $ = require('jquery')
require('bootstrap')
require('typeface-open-sans')

// attach jquery to window
window.$ = $

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

    // force reload on user browser button navigation
    $(window)
      .on('popstate', () => {
        window.location.reload(true)
      })
  })
