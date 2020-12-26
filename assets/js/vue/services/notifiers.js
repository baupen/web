import Noty from 'noty'

Noty.overrideDefaults({
  theme: 'bootstrap-v4',
  type: 'success'
})

const displaySuccess = function (successMessage) {
  new Noty({
    text: successMessage,
    timeout: 1000
  }).show()
}

const displayError = function (errorMessage) {
  new Noty({
    text: errorMessage,
    type: 'error',
    timeout: false
  }).show()
}

export { displaySuccess, displayError }
