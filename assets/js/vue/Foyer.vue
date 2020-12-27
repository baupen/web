<template>
  <div id="dispatch">
    <div class="btn-group mb-4">
      <button class="btn btn-primary"
              :disabled="unprocessedIssues.length > 0 || selectedIssues.length === 0"
              @click="registerSelectedIssues">
        {{$tc("foyer.actions.register_issues", selectedIssues.length)}}
      </button>
      <span class="btn btn-link" v-if="unprocessedIssues.length > 0">{{ unprocessedIssues.length }}</span>
    </div>

    <loading-indicator :spin="issueTableLoading">
      <issue-table
          :issues="issues"
          :craftsmen="craftsmen"
          :maps="maps"
          :proposed-selected-issues="issues"
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
      issues: null,
      craftsmen: null,
      maps: null,
      selectedIssues: [],
      unprocessedIssues: [],
    }
  },
  methods: {
    registerSelectedIssues: function () {
      const nowString = (new Date()).toISOString();
      this.unprocessedIssues = this.selectedIssues.map(issue => {
        return {issue, patch: {registeredAt: nowString, registeredBy: this.constructionManagerIri}}
      })

      const toBePatchedIssues = [...this.unprocessedIssues]
      this.patchIssues(toBePatchedIssues)
    },
    patchIssues(queue) {
      const payload = queue.pop()
      api.patch(payload.issue, payload.patch)
          .then(_ => {
                this.unprocessedIssues = this.unprocessedIssues.filter(e => e !== payload)

                if (queue.length === 0) {
                  displaySuccess(this.$t('foyer.messages.success.registered_issues'))
                } else {
                  this.patchIssues(queue)
                }
              }
          )
    },
  },
  computed: {
    issueTableLoading: function () {
      return this.issues === null || this.craftsmen === null || this.maps === null
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite

          api.getIssues(this.constructionSite, {isDeleted: false})
              .then(issues => this.issues = issues)

          api.getCraftsmen(this.constructionSite)
              .then(craftsmen => this.craftsmen = craftsmen)

          api.getMaps(this.constructionSite)
              .then(maps => this.maps = maps)
        })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
