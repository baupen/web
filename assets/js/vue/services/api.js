import axios from 'axios'
import Noty from 'noty'

axios.interceptors.response.use(
  response => {
    return response
  },
  error => {
    if (error.response) {
      new Noty({
        text: this.$t('messages.danger.request_failed') + ' (' + error.response.data.status + ': ' + error.response.data.detail + ')',
        theme: 'bootstrap-v4',
        type: 'error'
      }).show()
    } else {
      new Noty({
        text: this.$t('messages.danger.request_failed') + ' (' + error + ')',
        theme: 'bootstrap-v4',
        type: 'error'
      }).show()
    }

    console.log('request failed')
    console.log(error.response.data)
    return Promise.reject(error)
  }
)

const api = {
  loadConstructionSites (instance) {
    axios.get('/api/construction_sites')
      .then(response => {
        instance.constructionSites = response.data['hydra:member']
      })
      .catch(e => {
        instance.errors.push(e)
      })
  },
  loadConstructionManagers (instance) {
    axios.get('/api/construction_managers')
      .then(response => {
        instance.constructionManagers = response.data['hydra:member']
      })
      .catch(e => {
        instance.errors.push(e)
      })
  }
}

export { api }
