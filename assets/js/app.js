import '../css/app.scss'
import './vue/vuejs'

import { dom } from '@fortawesome/fontawesome-svg-core'

// noinspection ES6UnusedImports; imports code so modals etc work
import * as boostrap from 'bootstrap'

const QRious = require('qrious')

// register some basic usability functionality
document.addEventListener('DOMContentLoaded', () => {
  // Give instant feedback on form submission
  document.querySelectorAll('form')
    .forEach(form => {
      form.addEventListener('submit', function () {
        form.querySelectorAll('.btn')
          .forEach(button => {
            if (!button.classList.contains('no-disable')) {
              button.classList.add('disabled')
            }
          })
      })
    })

  dom.watch()

  const authenticationTokenCanvas = document.getElementsByClassName('authentication-token-canvas')
  if (authenticationTokenCanvas.length) {
    if (window.token) {
      renderQRCode(window.token)
    } else {
      fetch('/token') // request URL
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok ' + response.statusText)
          }

          renderQRCode(response.text())
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
