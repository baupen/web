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

    const authenticationTokenCanvas = document.getElementsByClassName('authentication-token-canvas')
    if (authenticationTokenCanvas.length) {
      if (window.token) {
        renderQRCode(window.token)
      } else {
        $.ajax('/token', // request url
          {
            success: function (token) {
              renderQRCode(token)
            }
          })
      }
    }
  })

function renderQRCode (token) {
  const payload = {
    token: token,
    origin: window.location.origin
  }

  const data = JSON.stringify(payload)

  const authenticationTokenCanvas = document.getElementsByClassName('authentication-token-canvas')
  Array.from(authenticationTokenCanvas).forEach(element => {
    // eslint-disable-next-line no-new
    new QRious({
      element,
      level: 'Q',
      value: data,
      size: 300
    })
  })

  const authenticationTokenLinks = document.getElementsByClassName('authentication-token-link')
  Array.from(authenticationTokenLinks).forEach(element => {
    element.setAttribute('href', 'mangelio://login?payload=' + btoa(data))
  })
}
