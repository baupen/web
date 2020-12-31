import axios from 'axios'
import { displaySuccess, displayError } from './notifiers'

const validImageTypes = ['image/jpeg', 'image/png', 'image/gif']

const iriToId = function (iri) {
  return iri.substr(iri.lastIndexOf('/') + 1)
}

const api = {
  setupErrorNotifications: function (translator) {
    axios.interceptors.response.use(
      response => {
        return response
      },
      error => {
        if (error === 'Request aborted') {
          // hide aborted errors (happens when navigating rapidly in firefox)
          return
        }

        console.log(error)

        let errorText = error
        if (error.response) {
          const response = error.response
          if (response.data['hydra:title'] && response.data['hydra:description']) {
            errorText = response.data['hydra:title'] + ': ' + response.data['hydra:description']
          } else {
            errorText = response.status
            console.log(response)
            if (response.data && response.data.detail) {
              errorText += ': ' + response.data.detail
            } else if (response.statusText) {
              errorText += ': ' + response.statusText
            }
          }
        }

        const errorMessage = translator('messages.danger.request_failed') + ' (' + errorText + ')'
        displayError(errorMessage)

        return Promise.reject(error)
      }
    )
  },
  _writeAllProperties: function (source, target) {
    for (const prop in source) {
      if (Object.prototype.hasOwnProperty.call(source, prop) && Object.prototype.hasOwnProperty.call(target, prop)) {
        target[prop] = source[prop]
      }
    }
  },
  _getConstructionSiteIriFromLocation: function () {
    const urlArray = window.location.pathname.split('/')
    urlArray.splice(3)
    return '/api' + urlArray.join('/')
  },
  _getConstructionSiteQuery: function (constructionSite) {
    return 'constructionSite=' + iriToId(constructionSite['@id'])
  },
  _getQueryString: function (query) {
    const queryList = []
    for (const entry in query) {
      if (Object.prototype.hasOwnProperty.call(query, entry)) {
        if (Array.isArray(query[entry])) {
          query[entry].forEach(item => {
            queryList.push(entry + '=' + item)
          })
        } else {
          queryList.push(entry + '=' + query[entry])
        }
      }
    }

    return queryList.join('&')
  },
  _getHydraCollection: function (url) {
    return new Promise(
      (resolve) => {
        axios.get(url)
          .then(response => {
            resolve(response.data['hydra:member'])
          })
      }
    )
  },
  _getPaginatedHydraCollection: function (url) {
    return new Promise(
      (resolve) => {
        axios.get(url)
          .then(response => {
            const payload = {
              items: response.data['hydra:member'],
              totalItems: response.data['hydra:totalItems']
            }
            resolve(payload)
          })
      }
    )
  },
  _getItem: function (url) {
    return new Promise(
      (resolve) => {
        axios.get(url)
          .then(response => {
            resolve(response.data)
          })
      }
    )
  },
  _postRaw: function (collectionUrl, post, successMessage = null) {
    return new Promise(
      (resolve) => {
        axios.post(collectionUrl, post)
          .then(response => {
            resolve(response.data)
            if (successMessage !== null) {
              displaySuccess(successMessage)
            }
          })
      }
    )
  },
  _post: function (collectionUrl, post, collection, successMessage = null) {
    return new Promise(
      (resolve) => {
        axios.post(collectionUrl, post)
          .then(response => {
            collection.push(response.data)
            if (successMessage !== null) {
              displaySuccess(successMessage)
            }
            resolve()
          })
      }
    )
  },
  _postMultipart: function (collectionUrl, file, fileKey, successMessage = null) {
    const formData = new FormData()
    formData.append(fileKey, file)

    return new Promise(
      (resolve) => {
        axios.post(collectionUrl, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
          .then(response => {
            if (successMessage !== null) {
              displaySuccess(successMessage)
            }
            resolve(response.data)
          })
      }
    )
  },
  _postAttachment: function (entity, file, fileKey, successMessage = null) {
    return new Promise(
      (resolve) => {
        this._postMultipart(entity['@id'] + '/' + fileKey, file, fileKey, successMessage)
          .then(response => {
            entity[fileKey + 'Url'] = response
            resolve()
          })
      }
    )
  },
  getMe: function () {
    return this._getItem('/api/me')
  },
  getConstructionSite: function () {
    const constructionSiteUrl = this._getConstructionSiteIriFromLocation()
    return this._getItem(constructionSiteUrl)
  },
  getConstructionManagers: function (constructionSite = null) {
    let urlSuffix = ''
    if (constructionSite) {
      urlSuffix = '?' + this._getConstructionSiteQuery(constructionSite)
    }
    return this._getHydraCollection('/api/construction_managers' + urlSuffix)
  },
  getConstructionSites: function () {
    return this._getHydraCollection('/api/construction_sites')
  },
  getPaginatedIssues: function (constructionSite, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(query)
    return this._getPaginatedHydraCollection('/api/issues?' + queryString)
  },
  getReportLink: function (constructionSite, reportQuery, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(reportQuery)
    queryString += '&' + this._getQueryString(query)
    return '/api/issues/report?' + queryString
  },
  getMaps: function (constructionSite, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(query)
    return this._getHydraCollection('/api/maps?' + queryString)
  },
  getCraftsmen: function (constructionSite, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(query)
    return this._getHydraCollection('/api/craftsmen?' + queryString)
  },
  getCraftsmenStatistics: function (constructionSite, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(query)
    return this._getItem('/api/craftsmen/statistics?' + queryString)
  },
  getEmailTemplates: function (constructionSite) {
    const queryString = this._getConstructionSiteQuery(constructionSite)
    return this._getHydraCollection('/api/email_templates?' + queryString)
  },
  getIssuesSummary: function (constructionSite) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&isDeleted=false'
    return this._getItem('/api/issues/summary?' + queryString)
  },
  getCraftsmenFeedEntries: function (constructionSite) {
    const queryString = '?constructionSite=' + iriToId(constructionSite['@id'])
    return this._getItem('/api/craftsmen/feed_entries' + queryString)
  },
  getIssuesFeedEntries: function (constructionSite, weeksInThePast = 0) {
    let queryString = '?constructionSite=' + iriToId(constructionSite['@id'])
    const week = 7 * 24 * 60 * 60 * 1000
    const lastChangedBefore = new Date(Date.now() - week * weeksInThePast)
    queryString += '&lastChangedAt[before]=' + lastChangedBefore.toISOString()
    const lastChangedAfter = new Date(Date.now() - week * (weeksInThePast + 1))
    queryString += '&lastChangedAt[after]=' + lastChangedAfter.toISOString()
    return this._getItem('/api/issues/feed_entries' + queryString)
  },
  patch: function (instance, patch, successMessage = null) {
    return new Promise(
      (resolve) => {
        axios.patch(instance['@id'], patch, { headers: { 'Content-Type': 'application/merge-patch+json' } })
          .then(response => {
            this._writeAllProperties(response.data, instance)
            resolve()
            if (successMessage !== null) {
              displaySuccess(successMessage)
            }
          })
      }
    )
  },
  delete: function (instance, successMessage = null) {
    return new Promise(
      (resolve) => {
        axios.delete(instance['@id'])
          .then(response => {
            // DELETE request might return entity in answer
            if (response.data['@id'] === instance) {
              this._writeAllProperties(response.data, instance)
            }

            resolve()
            if (successMessage !== null) {
              displaySuccess(successMessage)
            }
          })
      }
    )
  },
  postEmailTemplate: function (emailTemplate, collection, successMessage = null) {
    return this._post('/api/email_templates', emailTemplate, collection, successMessage)
  },
  postConstructionSite: function (constructionSite, collection, successMessage = null) {
    return this._post('/api/construction_sites', constructionSite, collection, successMessage)
  },
  postIssueImage: function (issue, image, successMessage = null) {
    return this._postAttachment(issue, image, 'image', successMessage)
  },
  postEmail: function (email) {
    return this._postRaw('/api/emails', email)
  }
}

export { api, iriToId, validImageTypes }
