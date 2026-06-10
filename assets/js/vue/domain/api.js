import { displaySuccess } from '../services/notifiers'
import { httpClient, restClient } from '../services/api'

const validImageTypes = ['image/jpeg', 'image/png', 'image/gif']
const validPdfFileTypes = ['application/pdf', 'application/x-pdf']
// ensure remains in sync what is checked server-side
const validSafeFileTypes = [
  ...validPdfFileTypes,
  ...validImageTypes,
  'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // word
  'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // excel
  'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', // presentation
  'application/vnd.ms-outlook', 'message/rfc822', // emails
  'text/html', 'text/plain', // text or plain
  'application/zip', 'application/octet-stream'// general
]
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

const router = {
  _getConstructionSiteBaseUrlFromLocation: function () {
    const urlArray = window.location.pathname.split('/')
    urlArray.splice(3)
    return urlArray.join('/')
  },
  constructionSiteDashboard: function (constructionSite) {
    return constructionSite['@id'].replace('/api', '') + '/dashboard'
  },
  currentFoyerUrl: function () {
    return this._getConstructionSiteBaseUrlFromLocation() + '/foyer'
  },
  currentDispatchUrl: function () {
    return this._getConstructionSiteBaseUrlFromLocation() + '/dispatch'
  },
  currentRegisterUrl: function (initialState = null) {
    const url = this._getConstructionSiteBaseUrlFromLocation() + '/register'
    const query = {}
    if (initialState) {
      query.state = initialState
    }

    return restClient._getQueryUrl(url, query)
  },
  getIssuesRenderLink: function (constructionSite, map, query = {}) {
    query.constructionSite = iriToId(constructionSite['@id'])
    query.map = iriToId(map['@id'])
    query.size = 'full'
    query.isDeleted = 'false'
    return restClient._getQueryUrl('/api/issues/render.jpg', query)
  }
}

const apiClient = {
  authenticate: function () {
    httpClient.additionalHeaders['X-AUTHENTICATION'] = window.token
  },
  patch: async function (instance, patch, successMessage = null) {
    const result = await restClient.patch(instance, patch)
    displaySuccessMessageIfExists(successMessage)

    return result
  },
  delete: async function (instance, successMessage = null) {
    const result = await restClient.delete(instance)
    displaySuccessMessageIfExists(successMessage)

    return result
  },
  post: async function (collectionUrl, post, successMessage = null) {
    const result = await restClient.post(collectionUrl, post)
    displaySuccessMessageIfExists(successMessage)
    return result
  },
  postAttachment: async function (entity, file, fileKey, successMessage = null) {
    const formData = new FormData()
    formData.append(fileKey, file)

    const init = {
      method: 'POST',
      body: formData
    }
    const response = await httpClient.request(entity['@id'] + '/' + fileKey, init)
    displaySuccessMessageIfExists(successMessage)

    entity[fileKey + 'Url'] = await response.text()
  }
}

apiClient.authenticate()

const api = {
  getById: function (id) {
    return restClient.get(id)
  },
  getConstructionSite: async function () {
    return window.constructionSite
  },
  getConstructionManagers: function (constructionSite = null) {
    const query = {}
    if (constructionSite) {
      query['constructionSites.id'] = iriToId(constructionSite['@id'])
    }
    return restClient.getCollection('/api/construction_managers', query)
  },
  getConstructionSites: function () {
    return restClient.getCollection('/api/construction_sites')
  },
  getPaginatedIssues: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id'])
    }
    return restClient.getPaginatedCollection('/api/issues', fullQuery)
  },
  getIssues: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id']),
      pagination: 0
    }
    return restClient.getCollection('/api/issues', fullQuery)
  },
  getReportLink: function (constructionSite, reportQuery, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id']),
      ...reportQuery
    }
    return restClient.get('/api/issues/report', fullQuery)
  },
  getMaps: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id'])
    }
    return restClient.getCollection('/api/maps', fullQuery)
  },
  getCraftsmen: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id'])
    }
    return restClient.getCollection('/api/craftsmen', fullQuery)
  },
  getCraftsmenStatistics: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id'])
    }
    return restClient.getCollection('/api/craftsmen/statistics', fullQuery)
  },
  getEmailTemplates: function (constructionSite) {
    const fullQuery = { constructionSite: iriToId(constructionSite['@id']) }
    return restClient.getCollection('/api/email_templates', fullQuery)
  },
  getIssuesSummary: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id']),
      isDeleted: false
    }
    return restClient.get('/api/issues/summary', fullQuery)
  },
  getIssuesTimeseries: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id']),
      isDeleted: false
    }
    return restClient.getCollection('/api/issues/timeseries', fullQuery)
  },
  getIssuesMapGroup: function (constructionSite, query = {}) {
    const fullQuery = {
      ...query,
      constructionSite: iriToId(constructionSite['@id']),
      group: 'map',
      isDeleted: false
    }
    return restClient.getCollection('/api/issues/group', fullQuery)
  },
  _getRecentlyChangedQuery: function (weeksInThePast = 0) {
    const week = 7 * 24 * 60 * 60 * 1000
    const lastChangedBefore = new Date(Date.now() - week * weeksInThePast)
    const lastChangedAfter = new Date(Date.now() - week * (weeksInThePast + 1))
    return {
      'lastChangedAt[before]': lastChangedBefore.toISOString(),
      'lastChangedAt[after]': lastChangedAfter.toISOString()
    }
  },
  getRecentlyChangedIssues: function (constructionSite, query = {}, weeksInThePast = 0) {
    const recentlyChangedQuery = this._getRecentlyChangedQuery(weeksInThePast)
    const fullQuery = {
      ...query,
      ...recentlyChangedQuery,
      constructionSite: iriToId(constructionSite['@id']),
      'order[lastChangedAt]': 'desc',
      pagination: 0,
      isDeleted: false
    }

    return restClient.getCollection('/api/issues', fullQuery)
  },
  getRecentIssueEvents: function (constructionSite, query = null, weeksInThePast = 0) {
    const recentlyChangedQuery = this._getRecentlyChangedQuery(weeksInThePast)
    const fullQuery = {
      ...query,
      ...recentlyChangedQuery,
      constructionSite: iriToId(constructionSite['@id']),
      isDeleted: false
    }

    return restClient.getCollection('/api/issue_events', fullQuery)
  },
  getIssuesRenderProbe: function (constructionSite, map, query = {}) {
    const link = router.getIssuesRenderLink(constructionSite, map, query)
    return httpClient.request(link, { headers: { 'X-EMPTY-RESPONSE-EXPECTED': '' } })
  },
  getTasksQuery: function (query) {
    return restClient.getCollection('/api/tasks', query)
  },
  getIssueEventsQuery: function (query) {
    return restClient.getCollection('/api/issue_events', query)
  },
  getIssueEvents: function (constructionSite, root, onlyContextualForChildren = false) {
    let queryString = '?constructionSite=' + iriToId(constructionSite['@id'])
    queryString += '&root=' + iriToId(root['@id'])
    if (onlyContextualForChildren) {
      queryString += '&contextualForChildren=true'
    }
    queryString += '&isDeleted=false'

    return restClient.getCollection('/api/issue_events', queryString)
  },
  postMap: function (map, successMessage = null) {
    return apiClient.post('/api/maps', map, successMessage)
  },
  postIssue: function (issue, successMessage = null) {
    return apiClient.post('/api/issues', issue, successMessage)
  },
  postConstructionManager: function (constructionManager, successMessage = null) {
    return apiClient.post('/api/construction_managers', constructionManager, successMessage)
  },
  postCraftsman: function (craftsman, successMessage = null) {
    return apiClient.post('/api/craftsmen', craftsman, successMessage)
  },
  postEmailTemplate: function (emailTemplate, successMessage = null) {
    return apiClient.post('/api/email_templates', emailTemplate, successMessage)
  },
  postConstructionSite: function (constructionSite, successMessage = null) {
    return apiClient.post('/api/construction_sites', constructionSite, successMessage)
  },
  postFilter: function (filter, successMessage = null) {
    return apiClient.post('/api/filters', filter, successMessage)
  },
  postTask: function (task, successMessage = null) {
    return apiClient.post('/api/tasks', task, successMessage)
  },
  postMapFile: function (map, file, successMessage = null) {
    return apiClient.postAttachment(map, file, 'file', successMessage)
  },
  postIssueEventFile: function (issueEvent, file, successMessage = null) {
    return apiClient.postAttachment(issueEvent, file, 'file', successMessage)
  },
  postIssueImage: function (issue, image, successMessage = null) {
    return apiClient.postAttachment(issue, image, 'image', successMessage)
  },
  postConstructionSiteImage: function (constructionSite, image, successMessage = null) {
    return apiClient.postAttachment(constructionSite, image, 'image', successMessage)
  },
  postCraftsmanEmail: function (email, successMessage = null) {
    return apiClient.post('/api/craftsman_emails', email, successMessage)
  },
  postIssueEvent: function (issueEvent, successMessage = null) {
    return apiClient.post('/api/issue_events', issueEvent, successMessage)
  },
  patch: function (instance, patch, successMessage = null) {
    return apiClient.patch(instance, patch, successMessage)
  },
  delete: async function (instance, successMessage = null) {
    return apiClient.delete(instance, successMessage)
  },
}

export {
  router,
  api,
  apiClient,
  addNonDuplicatesById,
  iriToId,
  validImageTypes,
  validPdfFileTypes,
  validSafeFileTypes,
  maxIssuesPerReport
}
