import axios from 'axios'
import { displaySuccess, displayError } from './notifiers'

const validImageTypes = ['image/jpeg', 'image/png', 'image/gif']
const validFileTypes = ['application/pdf']
const maxIssuesPerReport = 800

const iriToId = function (iri) {
  return iri.substr(iri.lastIndexOf('/') + 1)
}

const displaySuccessMessageIfExists = function (successMessage = null) {
  if (successMessage) {
    displaySuccess(successMessage)
  }
}

const addNonDuplicatesById = function (originalCollection, addCollection) {
  addCollection.forEach(add => {
    if (!originalCollection.find(o => o['@id'] === add['@id'])) {
      originalCollection.push(add)
    }
  })
}

const api = {
  setupErrorNotifications: function (translator) {
    axios.interceptors.response.use(
      response => {
        return response
      },
      error => {
        /* eslint-disable-next-line eqeqeq */
        if (error == 'Error: Request aborted') {
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
            if (response.data && response.data.detail) {
              errorText += ': ' + response.data.detail
            } else if (response.statusText) {
              errorText += ': ' + response.statusText
            }
          }
        }

        const errorMessage = translator('_api.request_failed') + ' (' + errorText + ')'
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
  _getConstructionSiteBaseUrlFromLocation: function () {
    const urlArray = window.location.pathname.split('/')
    urlArray.splice(3)
    return urlArray.join('/')
  },
  _getConstructionSiteIriFromLocation: function () {
    const urlArray = window.location.pathname.split('/')
    urlArray.splice(3)
    return '/api' + urlArray.join('/')
  },
  _getTokenFromLocation: function () {
    const urlArray = window.location.pathname.split('/')
    return urlArray[2] // location of the form "domain.com/resolve/<token>"
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
  _getEmptyResponse: function (url) {
    return new Promise(
      (resolve) => {
        axios.get(url, { headers: { 'X-EMPTY-RESPONSE-EXPECTED': '' } })
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
            displaySuccessMessageIfExists(successMessage)
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
            displaySuccessMessageIfExists(successMessage)
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
            displaySuccessMessageIfExists(successMessage)
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
  _getMe: function (authenticationToken) {
    axios.defaults.headers['X-AUTHENTICATION'] = authenticationToken
    return new Promise(
      (resolve) => {
        if (window.me && window.token === authenticationToken) {
          resolve(window.me)
        } else {
          axios.get('/api/me')
            .then(response => {
              resolve(response.data)
            })
        }
      }
    )
  },
  currentFoyerUrl: function () {
    return this._getConstructionSiteBaseUrlFromLocation() + '/foyer'
  },
  currentDispatchUrl: function () {
    return this._getConstructionSiteBaseUrlFromLocation() + '/dispatch'
  },
  currentRegisterUrl: function (initialState = null) {
    let url = this._getConstructionSiteBaseUrlFromLocation() + '/register'

    if (initialState) {
      url += '?state=' + initialState
    }

    return url
  },
  authenticateFromUrl: function () {
    const token = this._getTokenFromLocation()
    return this._getMe(token)
  },
  authenticate: function () {
    return new Promise(
      (resolve) => {
        if (window.token) {
          this._getMe(window.token)
            .then(response => {
              resolve(response)
            })
        } else {
          axios.get('/token')
            .then(response => {
              this._getMe(response.data)
                .then(response => {
                  resolve(response)
                })
            })
        }
      }
    )
  },
  getById: function (id) {
    return this._getItem(id)
  },
  getConstructionSite: function () {
    return new Promise(
      (resolve) => {
        if (window.constructionSite) {
          resolve(window.constructionSite)
        } else {
          const constructionSiteUrl = this._getConstructionSiteIriFromLocation()
          axios.get(constructionSiteUrl)
            .then(response => {
              resolve(response.data)
            })
        }
      }
    )
  },
  getConstructionManagers: function (constructionSite = null) {
    let urlSuffix = ''
    if (constructionSite) {
      urlSuffix = '?constructionSites.id=' + iriToId(constructionSite['@id'])
    }
    return this._getHydraCollection('/api/construction_managers' + urlSuffix)
  },
  getConstructionSites: function (constructionManager = null) {
    let urlSuffix = ''
    if (constructionManager) {
      urlSuffix = '?constructionManagers.id=' + iriToId(constructionManager['@id'])
    }
    return this._getHydraCollection('/api/construction_sites' + urlSuffix)
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
    return this._getItem('/api/issues/report?' + queryString)
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
    return this._getHydraCollection('/api/craftsmen/statistics?' + queryString)
  },
  getEmailTemplates: function (constructionSite) {
    const queryString = this._getConstructionSiteQuery(constructionSite)
    return this._getHydraCollection('/api/email_templates?' + queryString)
  },
  getIssuesSummary: function (constructionSite, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(query)
    queryString += '&isDeleted=false'
    return this._getItem('/api/issues/summary?' + queryString)
  },
  getIssuesTimeseries: function (constructionSite, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&' + this._getQueryString(query)
    queryString += '&isDeleted=false'
    return this._getHydraCollection('/api/issues/timeseries?' + queryString)
  },
  getIssuesGroup: function (constructionSite, group, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&group=' + group
    queryString += '&' + this._getQueryString(query)
    queryString += '&isDeleted=false'
    return this._getHydraCollection('/api/issues/group?' + queryString)
  },
  getCraftsmenFeedEntries: function (constructionSite) {
    const queryString = '?constructionSite=' + iriToId(constructionSite['@id'])
    return this._getHydraCollection('/api/craftsmen/feed_entries' + queryString)
  },
  getIssuesFeedEntries: function (constructionSite, weeksInThePast = 0, query = null) {
    let queryString = '?constructionSite=' + iriToId(constructionSite['@id'])
    const week = 7 * 24 * 60 * 60 * 1000
    const lastChangedBefore = new Date(Date.now() - week * weeksInThePast)
    queryString += '&lastChangedAt[before]=' + lastChangedBefore.toISOString()
    const lastChangedAfter = new Date(Date.now() - week * (weeksInThePast + 1))
    queryString += '&lastChangedAt[after]=' + lastChangedAfter.toISOString()

    if (query) {
      queryString += '&' + this._getQueryString(query)
    }

    return this._getHydraCollection('/api/issues/feed_entries' + queryString)
  },
  getIssuesRenderLink: function (constructionSite, map, query = {}) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&map=' + iriToId(map['@id'])
    queryString += '&' + this._getQueryString(query)
    queryString += '&isDeleted=false'
    return '/api/issues/render.jpg?' + queryString
  },
  getIssuesRenderProbe: function (constructionSite, map, query = {}) {
    const link = this.getIssuesRenderLink(constructionSite, map, query)
    return this._getEmptyResponse(link)
  },
  patch: function (instance, patch, successMessage = null) {
    return new Promise(
      (resolve) => {
        axios.patch(instance['@id'], patch, { headers: { 'Content-Type': 'application/merge-patch+json' } })
          .then(response => {
            this._writeAllProperties(response.data, instance)
            resolve()
            displaySuccessMessageIfExists(successMessage)
          })
      }
    )
  },
  delete: function (instance, successMessage = null) {
    return new Promise(
      (resolve) => {
        axios.delete(instance['@id'])
          .then(response => {
            if (response.status === 204) {
              // if isDeleted property exists, this is an entity with soft delete
              if (Object.prototype.hasOwnProperty.call(instance, 'isDeleted')) {
                instance.isDeleted = true
              }
            }

            resolve()
            displaySuccessMessageIfExists(successMessage)
          })
      }
    )
  },
  postMap: function (map, successMessage = null) {
    return this._postRaw('/api/maps', map, successMessage)
  },
  postConstructionManager: function (constructionManager, successMessage = null) {
    return this._postRaw('/api/construction_managers', constructionManager, successMessage)
  },
  postCraftsman: function (craftsman, successMessage = null) {
    return this._postRaw('/api/craftsmen', craftsman, successMessage)
  },
  postEmailTemplate: function (emailTemplate, collection, successMessage = null) {
    return this._post('/api/email_templates', emailTemplate, collection, successMessage)
  },
  postConstructionSite: function (constructionSite, successMessage = null) {
    return this._postRaw('/api/construction_sites', constructionSite, successMessage)
  },
  postFilter: function (filter, successMessage = null) {
    return this._postRaw('/api/filters', filter, successMessage)
  },
  postMapFile: function (map, file, successMessage = null) {
    return this._postAttachment(map, file, 'file', successMessage)
  },
  postIssueImage: function (issue, image, successMessage = null) {
    return this._postAttachment(issue, image, 'image', successMessage)
  },
  postConstructionSiteImage: function (constructionSite, image, successMessage = null) {
    return this._postAttachment(constructionSite, image, 'image', successMessage)
  },
  postEmail: function (email) {
    return this._postRaw('/api/emails', email)
  }
}

export { api, addNonDuplicatesById, iriToId, validImageTypes, validFileTypes, maxIssuesPerReport }
