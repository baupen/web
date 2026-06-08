const entityFormatter = {
  name: function (instance) {
    if (instance['@id'].includes('construction_managers')) {
      return constructionManagerFormatter.name(instance)
    } else if (instance['@id'].includes('craftsmen')) {
      return instance.company
    }

    return ''
  }
}

const constructionManagerFormatter = {
  name: function (instance) {
    if (!instance) {
      return ''
    }

    const name = [instance.givenName, instance.familyName]
      .filter(e => e)
      .join(' ')
    return name ?? instance.email
  }
}

const constructionSiteFormatter = {
  address: function (instance) {
    const address = []
    if (instance.streetAddress) {
      address.push(instance.streetAddress)
    }

    const plzAndPlace = instance.postalCode + ' ' + instance.locality
    if (plzAndPlace.trim()) {
      address.push(plzAndPlace.trim())
    }

    return address
  }
}

const mapFormatter = {
  originalFilename: function (instance) {
    if (!instance.fileUrl) {
      return null
    }

    let currentFilename = instance.fileUrl.substr(instance.fileUrl.lastIndexOf('/') + 1)
    if (currentFilename.indexOf('_duplicate_') > 0) {
      currentFilename = currentFilename.substr(0, currentFilename.indexOf('_duplicate_'))
    }

    return decodeURI(currentFilename)
  }
}

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

export { dateTimeFormatter, entityFormatter, constructionManagerFormatter, constructionSiteFormatter, mapFormatter }
