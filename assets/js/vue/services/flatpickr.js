import moment from 'moment'

import flatPickr from 'vue-flatpickr-component/src/component'

const dateConfig = {
  altInput: true,
  allowInput: true,
  altFormat: 'DD.MM.YYYY',
  dateFormat: 'iso',
  parseDate: (datestr, format) => {
    if (format === 'iso') {
      return moment(datestr).toDate()
    } else {
      return moment(datestr, format, true).toDate()
    }
  },
  formatDate: (date, format, locale) => {
    if (format === 'iso') {
      return moment(date).format('DD.MM.YYYY')
    } else {
      return moment(date).format(format)
    }
    // locale can also be used
  },
  enableTime: false
}

const dateTimeConfig = {
  altInput: true,
  altFormat: 'DD.MM.YYYY HH:mm',
  dateFormat: 'iso',
  parseDate: (datestr, format) => {
    if (format === 'iso') {
      return moment(datestr).toDate()
    } else {
      return moment(datestr, format, true).toDate()
    }
  },
  formatDate: (date, format, locale) => {
    if (format === 'iso') {
      return moment(date).format()
    } else {
      return moment(date).format(format)
    }
    // locale can also be used
  },
  enableTime: true,
  time_24hr: true
}

export { dateConfig, dateTimeConfig, flatPickr }
