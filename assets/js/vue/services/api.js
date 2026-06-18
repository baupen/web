import { displayError } from './notifiers'

const httpClient = {
  additionalHeaders: {},
  _handleError: async function (response) {
    let responseData

    try {
      responseData = await response.json()
    } catch (e) {
      // response body is not JSON
      console.log(e)
      displayError('Failed with unknown error')
      throw e
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
  request: async function (url, init = {}) {
    let response

    const fullInit = {
      ...init,
      headers: {
        ...this.additionalHeaders,
        ...init.headers
      }
    }

    try {
      response = await fetch(url, fullInit)
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

    const response = await httpClient.request(url, init)

    if (response.status === 204) {
      return null
    }

    return response.json()
  },
  _getQueryUrl: function (url, query) {
    const queryUrl = new URL(url, window.location.origin)
    Object.keys(query).forEach(key => {
      if (Array.isArray(query[key])) {
        query[key].forEach(value => queryUrl.searchParams.append(key, value))
      } else {
        queryUrl.searchParams.append(key, query[key])
      }
    })
    return queryUrl.toString()
  },
  getCollection: async function (url, query = {}, options = {}) {
    const fullUrl = this._getQueryUrl(url, query)
    const responseData = await this._jsonRequest(fullUrl, options)
    return responseData['hydra:member']
  },
  getPaginatedCollection: async function (url, query = {}, options = {}) {
    const fullUrl = this._getQueryUrl(url, query)
    const responseData = await this._jsonRequest(fullUrl, options)
    return {
      items: responseData['hydra:member'],
      totalItems: responseData['hydra:totalItems']
    }
  },
  get: async function (url, query = {}, options = {}) {
    const fullUrl = this._getQueryUrl(url, query)
    return this._jsonRequest(fullUrl, options)
  },
  post: async function (collectionUrl, post, options = {}) {
    return this._jsonRequest(collectionUrl, {
      ...options,
      headers: {
        'Content-Type': 'application/ld+json',
        ...options.headers
      },
      method: 'POST'
    }, post)
  },
  patch: async function (instance, patch, options = {}) {
    const responseData = await this._jsonRequest(instance['@id'], {
      ...options,
      headers: {
        'Content-Type': 'application/merge-patch+json',
        ...options.headers
      },
      method: 'PATCH'
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
  delete: async function (instance, options = {}) {
    const responseData = await this._jsonRequest(instance['@id'], {
      ...options,
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

    instance.isDeleted = true
  }
}

export { restClient, httpClient }
