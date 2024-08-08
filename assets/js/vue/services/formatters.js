const entityFormatter = {
  name: function (instance) {
    if (instance['@id'].includes('construction_manager')) {
      return constructionManagerFormatter.name(instance)
    } else if (instance['@id'].includes('craftsman')) {
      return instance.name
    }

    return ''
  }
}

const constructionManagerFormatter = {
  name: function (instance) {
    return [instance.givenName, instance.familyName]
      .filter(e => e)
      .join(' ')
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

export { entityFormatter, constructionManagerFormatter, constructionSiteFormatter, mapFormatter }
