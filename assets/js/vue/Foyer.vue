<template>
  <div id="dispatch">
    <div class="btn-group mb-4">
      <button class="btn btn-primary"
              :disabled="preRegisterIssues.length > 0 || selectedIssues.length === 0"
              @click="registerSelectedIssues">
        {{ $tc("foyer.actions.register_issues", selectedIssues.length) }}
      </button>
      <span class="btn btn-link" v-if="preRegisterIssues.length > 0">{{ preRegisterIssues.length }}</span>
    </div>

    <loading-indicator :spin="constructionSite === null">
      <issue-table
          :construction-site="constructionSite"
          :default-filter="{isDeleted: false, state: 0}"
          @selected="selectedIssues = $event"/>
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import LoadingIndicator from './components/View/LoadingIndicator'
import {displaySuccess} from './services/notifiers'
import IssueTable from "./components/IssueTable";

export default {
  components: {
    IssueTable,
    LoadingIndicator,
  },
  data() {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      selectedIssues: [],
      preRegisterIssues: [],
    }
  },
  methods: {
    registerSelectedIssues: function () {
      const nowString = (new Date()).toISOString();
      this.preRegisterIssues = this.selectedIssues.map(issue => {
        return {issue, patch: {registeredAt: nowString, registeredBy: this.constructionManagerIri}}
      })

      this.processUnregisteredIssues()
    },
    processUnregisteredIssues() {
      const payload = this.preRegisterIssues[0]
      api.patch(payload.issue, payload.patch)
          .then(_ => {
                this.preRegisterIssues.shift()
                this.issues = this.issues.filter(i => i !== payload.issue);
                this.selectedIssues = this.selectedIssues.filter(i => i !== payload.issue);

                if (this.preRegisterIssues.length === 0) {
                  displaySuccess(this.$t('foyer.messages.success.registered_issues'))
                } else {
                  this.processUnregisteredIssues()
                }
              }
          )
    },
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite
        })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
