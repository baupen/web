const locale = document.documentElement.lang.substring(0, 2)
const dateTimeFormatter = {
  dateShort: function (value) {
    return new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: '2-digit'
    }).format(new Date(value))
  },
  date: function (value) {
    return new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }).format(new Date(value))
  },
  dateTime: function (value) {
    return new Intl.DateTimeFormat(locale, {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(new Date(value))
  },
  isoFilename: function (value) {
    return value.toISOString()
      .slice(0, 16)
      .replace('T', '-')
      .replace(':', '')
  }
}

export { dateTimeFormatter }
