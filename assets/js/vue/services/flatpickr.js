import flatPickr from 'vue-flatpickr-component/src/component'
import Flatpickr from 'flatpickr'
import { German } from 'flatpickr/dist/l10n/de'
Flatpickr.localize(German)

const dateConfig = {
  altInput: true,
  altFormat: 'd.m.Y', // what is displayed to the user; in effect because altInput === true
  dateFormat: 'Y-m-d', // what the input value actually is (v-model value; what is sent to the server)
  allowInput: true, // user can manually edit the input
  enableTime: false // no time selection possible
}

const dateTimeConfig = {
  altInput: true,
  altFormat: 'd.m.Y H:i', // what is displayed to the user; in effect because altInput === true
  dateFormat: 'Z', // what the input value actually is (v-model value; what is sent to the server)
  allowInput: true, // user can manually edit the input
  enableTime: true // no time selection possible
}

const toggleAnchorValidity = function (anchor, field) {
  if (!anchor) {
    return
  }

  const visibleInput = anchor.parentElement.childNodes[4]

  const showIsValid = field.dirty && !field.errors.length
  if (showIsValid) {
    visibleInput.classList.add('is-valid')
  } else {
    visibleInput.classList.remove('is-valid')
  }

  const showIsInvalid = field.dirty && field.errors.length
  if (showIsInvalid) {
    visibleInput.classList.add('is-invalid')
  } else {
    visibleInput.classList.remove('is-invalid')
  }
}

export { dateConfig, dateTimeConfig, flatPickr, toggleAnchorValidity }
