import { displayError } from './notifiers'

const errorHandlingClient = {
  _handleError: async function (response) {
    let responseData = null

    try {
      responseData = await response.json()
    } catch (e) {
      // response body is not JSON
    }

    let errorText = response.status
    if (responseData && responseData.title && responseData.description) {
      errorText = responseData.title + ': ' + responseData.description
    } else if (responseData && responseData.detail) {
      errorText += ': ' + responseData.detail
    } else if (response.statusText) {
      errorText += ': ' + response.statusText
    }

    const error = new Error(errorText)
    error.response = response
    error.data = responseData

    console.log(error)
    displayError('Failed: ' + errorText)

    throw error
  },
  _request: async function (url, init = {}) {
    let response

    try {
      response = await fetch(url, init)
    } catch (error) {
      if (error.name === 'AbortError') {
        // hide aborted errors (happens when navigating rapidly in firefox)
        return null
      }

      console.log(error)
      displayError('Failed: ' + error)

      throw error
    }

    if (!response.ok) {
      await this._handleError(response)
    }

    return response
  }
}

const restClient = {
  _jsonRequest: async function (url, options = {}, body = undefined) {
    const init = { ...options }
    if (body) {
      const normalizedBody = { ...body }
      for (const prop in normalizedBody) {
        if (Object.prototype.hasOwnProperty.call(normalizedBody, prop) && (normalizedBody[prop] === undefined || normalizedBody[prop] === '')) {
          normalizedBody[prop] = null
        }
      }
      init.body = JSON.stringify(normalizedBody)
    }

    const response = await errorHandlingClient._request(url, init)

    if (response.status === 204) {
      return null
    }

    return response.json()
  },
  _getQueryUrl: function (url, query) {
    const queryUrl = new URL(url, window.location.origin)
    Object.keys(query).forEach(key => {
      if (Array.isArray(query[key])) {
        query[key].forEach(value => queryUrl.searchParams.append(key + '[]', value))
      } else {
        queryUrl.searchParams.append(key, query[key])
      }
    })
    return queryUrl.toString()
  },
  getCollection: async function (url, query) {
    const fullUrl = this._getQueryUrl(url, query)
    const responseData = await this._jsonRequest(fullUrl)
    return responseData.member
  },
  getPaginatedCollection: async function (url, query) {
    const fullUrl = this._getQueryUrl(url, query)
    const responseData = await this._jsonRequest(fullUrl)
    return {
      items: responseData.member,
      totalItems: responseData.totalItems
    }
  },
  get: async function (url) {
    return this._jsonRequest(url)
  },
  post: async function (collectionUrl, post) {
    return this._jsonRequest(collectionUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/ld+json' }
    }, post)
  },
  patch: async function (instance, patch) {
    const responseData = await this._jsonRequest(instance['@id'], {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/merge-patch+json' }
    }, patch)

    // null values may not be delivered in response, hence check what values are only in patch and apply them
    for (const prop in patch) {
      if (Object.prototype.hasOwnProperty.call(patch, prop) && !Object.prototype.hasOwnProperty.call(responseData, prop)) {
        instance[prop] = patch[prop]
      }
    }

    for (const prop in responseData) {
      if (Object.prototype.hasOwnProperty.call(responseData, prop)) {
        instance[prop] = responseData[prop]
      }
    }
  },
  delete: async function (instance) {
    const responseData = await this._jsonRequest(instance['@id'], {
      method: 'DELETE'
    })

    if (responseData) {
      // for some entities, DELETE is overridden to be soft delete
      for (const prop in responseData) {
        if (Object.prototype.hasOwnProperty.call(responseData, prop)) {
          instance[prop] = responseData[prop]
        }
      }
    }
  }
}

export { restClient }
