import axios from 'axios'
import Noty from 'noty'

const api = {
  setupErrorNotifications: function (translator) {
    axios.interceptors.response.use(
      response => {
        return response
      },
      error => {
        console.log(error)

        let errorText = error
        if (error.response) {
          const response = error.response
          if (response.data) {
            const data = response.data
            errorText = data['hydra:title'] + ': ' + data['hydra:description']
          } else {
            errorText = response.status + ': ' + response.statusText
          }
        }

        new Noty({
          text: translator('messages.danger.request_failed') + ' (' + errorText + ')',
          theme: 'bootstrap-v4',
          type: 'error'
        }).show()

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
    return 'constructionSite=' + this._getIdFromIri(constructionSite)
  },
  _getQueryString: function (query) {
    const queryList = []
    for (const entry in query) {
      if (Object.prototype.hasOwnProperty.call(query, entry)) {
        queryList.push(entry + '=' + query[entry])
      }
    }

    return queryList.join('&')
  },
  _getIdFromIri: function (object) {
    const iri = object['@id']
    return iri.substr(iri.lastIndexOf('/') + 1)
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
  getMe: function () {
    return this._getItem('/api/me')
  },
  getConstructionSite: function () {
    const constructionSiteUrl = this._getConstructionSiteIriFromLocation()
    return this._getItem(constructionSiteUrl)
  },
  getConstructionManagers: function () {
    return this._getHydraCollection('/api/construction_managers')
  },
  getConstructionSites: function () {
    return this._getHydraCollection('/api/construction_sites')
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
  getIssuesSummary: function (constructionSite) {
    let queryString = this._getConstructionSiteQuery(constructionSite)
    queryString += '&isDeleted=false'
    return this._getItem('/api/issues/summary?' + queryString)
  },
  getCraftsmenFeedEntries: function (constructionSite) {
    const queryString = '?constructionSite=' + this._getIdFromIri(constructionSite)
    return this._getItem('/api/craftsmen/feed_entries' + queryString)
  },
  getIssuesFeedEntries: function (constructionSite, weeksInThePast = 0) {
    let queryString = '?constructionSite=' + this._getIdFromIri(constructionSite)
    const week = 7 * 24 * 60 * 60 * 1000
    const lastChangedBefore = new Date(Date.now() - week * weeksInThePast)
    queryString += '&lastChangedAt[before]=' + lastChangedBefore.toISOString()
    const lastChangedAfter = new Date(Date.now() - week * (weeksInThePast + 1))
    queryString += '&lastChangedAt[after]=' + lastChangedAfter.toISOString()
    return this._getItem('/api/issues/feed_entries' + queryString)
  },
  patch: function (instance, patch) {
    return new Promise(
      (resolve) => {
        axios.patch(instance['@id'], patch, { headers: { 'Content-Type': 'application/merge-patch+json' } })
          .then(response => {
            this._writeAllProperties(response.data, instance)
            resolve()
          })
      }
    )
  },
  postRaw: function (collectionUrl, post) {
    return new Promise(
      (resolve) => {
        axios.post(collectionUrl, post)
          .then(response => {
            resolve(response.data)
          })
      }
    )
  },
  post: function (collectionUrl, post, collection) {
    return new Promise(
      (resolve) => {
        axios.post(collectionUrl, post)
          .then(response => {
            collection.push(response.data)
            resolve()
          })
      }
    )
  }
}

export { api }
