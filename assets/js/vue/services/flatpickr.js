import flatPickr from 'vue-flatpickr-component/src/component'

const dateConfig = {
  altInput: true,
  altFormat: 'd.m.Y', // what is displayed to the user; in effect because altInput === true
  dateFormat: 'Y-m-d', // what the input value actually is (v-model value; what is sent to the server)
  allowInput: true, // user can manually edit the input
  enableTime: false // no time selection possible
}

export { dateConfig, flatPickr }
