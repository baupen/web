import '../css/app.scss'
import './vue/vuejs'

import { dom } from '@fortawesome/fontawesome-svg-core'

const $ = require('jquery')
window.$ = $
require('bootstrap')
require('typeface-open-sans')

const QRious = require('qrious')

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

    dom.watch()

    const authenticationTokenPlaceholders = document.getElementsByClassName('authentication-token-canvas')
    if (authenticationTokenPlaceholders.length) {
      $.ajax('/token', // request url
        {
          success: function (token) {
            const payload = {
              token: token,
              origin: window.location.origin
            }

            const data = JSON.stringify(payload)

            for (const index in authenticationTokenPlaceholders) {
              const authenticationTokenPlaceholder = authenticationTokenPlaceholders[index]

              new QRious({
                element: authenticationTokenPlaceholder,
                level: 'Q',
                value: data,
                size: 300
              })
            }
          }
        })
    }
  })
