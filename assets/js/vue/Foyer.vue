<template>
  <div id="dispatch">
    <div v-if="issueTableLoading || issues.length > 0" class="btn-group mb-4">
      <button class="btn btn-primary"
              :disabled="preRegisterIssues.length > 0 || selectedIssues.length === 0"
              @click="registerSelectedIssues">
        {{ $tc("foyer.actions.register_issues", selectedIssues.length) }}
      </button>
      <span class="btn btn-link" v-if="preRegisterIssues.length > 0">{{ preRegisterIssues.length }}</span>
    </div>

    <loading-indicator :spin="issueTableLoading">
      <issue-table
          v-if="issues.length"
          :issues="issues"
          :craftsmen="craftsmen"
          :maps="maps"
          :construction-managers="constructionManagers"
          @selected="selectedIssues = $event"
          @deleted="deletedIssue"/>
      <span v-else class="alert alert-info">
        {{ $t('foyer.no_unregistered_issues')}}
      </span>
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
      issues: null,
      craftsmen: null,
      maps: null,
      constructionManagers: null,
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
    deletedIssue(issue) {
      this.issues = this.issues.filter(i => i !== issue)
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
  computed: {
    issueTableLoading: function () {
      return this.issues === null || this.craftsmen === null || this.maps === null || this.constructionManagers === null
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite

          api.getIssues(this.constructionSite, {isDeleted: false, state: 0})
              .then(issues => this.issues = issues)

          api.getCraftsmen(this.constructionSite)
              .then(craftsmen => this.craftsmen = craftsmen)

          api.getMaps(this.constructionSite)
              .then(maps => this.maps = maps)

          api.getConstructionManagers(this.constructionSite)
              .then(constructionManagers => this.constructionManagers = constructionManagers)
        })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
