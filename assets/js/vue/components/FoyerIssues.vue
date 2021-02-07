<template>
  <div id="dispatch">
    <div class="btn-group mb-4">
      <button class="btn btn-primary"
              :disabled="preRegisterIssues.length > 0 || selectedIssues.length === 0"
              @click="registerSelectedIssues">
        {{ $tc('foyer.actions.register_issues', selectedIssues.length) }}
      </button>
      <span class="btn btn-link" v-if="preRegisterIssues.length > 0">{{ preRegisterIssues.length }}</span>
    </div>

    <issues-table
        :construction-site="constructionSite"
        :force-state="1"
        @selected="selectedIssues = $event" />
  </div>
</template>

<script>

import LoadingIndicator from './Library/View/LoadingIndicator'
import IssuesTable from './View/IssuesTable'
import { api } from '../services/api'
import { displaySuccess } from '../services/notifiers'

export default {
  components: {
    IssuesTable,
    LoadingIndicator
  },
  data () {
    return {
      selectedIssues: [],
      preRegisterIssues: [],
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    registerSelectedIssues: function () {
      const nowString = (new Date()).toISOString()
      this.preRegisterIssues = this.selectedIssues.map(issue => {
        return { issue,
          patch: {
            registeredAt: nowString,
            registeredBy: this.constructionManagerIri
          }
        }
      })

      this.processUnregisteredIssues()
    },
    processUnregisteredIssues () {
      const payload = this.preRegisterIssues[0]
      api.patch(payload.issue, payload.patch)
          .then(_ => {
                this.preRegisterIssues.shift()
                this.selectedIssues = this.selectedIssues.filter(i => i !== payload.issue)

                if (this.preRegisterIssues.length === 0) {
                  displaySuccess(this.$t('foyer.messages.success.registered_issues'))
                } else {
                  this.processUnregisteredIssues()
                }
              }
          )
    },
  }
}

</script>
