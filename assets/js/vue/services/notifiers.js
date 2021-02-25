import Noty from 'noty'

Noty.overrideDefaults({
  theme: 'bootstrap-v4',
  type: 'success'
})

const displaySuccess = function (successMessage) {
  new Noty({
    text: successMessage,
    timeout: 2000
  }).show()
}

const displayWarning = function (warningMessage) {
  new Noty({
    text: warningMessage,
    type: 'warning',
    timeout: false
  }).show()
}

const displayError = function (errorMessage) {
  new Noty({
    text: errorMessage,
    type: 'error',
    timeout: false
  }).show()
}

export { displaySuccess, displayWarning, displayError }
