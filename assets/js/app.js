import '../css/app.scss'
import './vue/vuejs'

import { dom } from '@fortawesome/fontawesome-svg-core'

const $ = require('jquery')
window.$ = $
require('bootstrap')

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
  // same payload also iOSLoginLink Twig Extension
  const payload = {
    token: token,
    origin: window.location.origin
  }

  const href = 'mangelio://login?payload=' + btoa(JSON.stringify(payload))

  const authenticationTokenCanvas = document.getElementsByClassName('authentication-token-canvas')
  Array.from(authenticationTokenCanvas).forEach(element => {
    // eslint-disable-next-line no-new
    new QRious({
      element,
      level: 'Q',
      value: href,
      size: 210
    })
  })

  const authenticationTokenLinks = document.getElementsByClassName('authentication-token-link')
  Array.from(authenticationTokenLinks).forEach(element => {
    element.setAttribute('href', href)
  })
}
