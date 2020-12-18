import axios from 'axios'
import Noty from 'noty'

const api = {
  setupErrorNotifications (instance) {
    axios.interceptors.response.use(
      response => {
        return response
      },
      error => {
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
          text: instance.$t('messages.danger.request_failed') + ' (' + errorText + ')',
          theme: 'bootstrap-v4',
          type: 'error'
        }).show()

        return Promise.reject(error)
      }
    )
  },
  loadMe (instance) {
    axios.get('/api/me')
      .then(response => {
        for (const prop in response.data) {
          if (Object.prototype.hasOwnProperty.call(response.data, prop)) {
            instance[prop] = response.data[prop]
          }
        }
      })
  },
  loadConstructionSites (instance) {
    axios.get('/api/construction_sites')
      .then(response => {
        instance.constructionSites = response.data['hydra:member']
      })
  },
  loadConstructionManagers (instance) {
    axios.get('/api/construction_managers')
      .then(response => {
        instance.constructionManagers = response.data['hydra:member']
      })
  }
}

export { api }
