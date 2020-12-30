<template>
  <div id="register">
    <div class="btn-group mb-4">
      <export-issues-button
          :disabled="exportDisabled"
          :query="query" :queried-issue-count="queriedIssuesCount" :selected-issues="selectedIssues" />
    </div>

    <loading-indicator :spin="constructionSite === null">
      <issue-table
          :construction-site="constructionSite"
          :default-filter="{isDeleted: false}"
          @selected="selectedIssues = $event"
          @query="query = $event"
          @queried-issue-count="queriedIssuesCount = $event"/>
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import LoadingIndicator from './components/View/LoadingIndicator'
import {displaySuccess} from './services/notifiers'
import IssueTable from "./components/IssueTable";
import ExportIssuesButton from "./components/ExportIssuesButton";

export default {
  components: {
    ExportIssuesButton,
    IssueTable,
    LoadingIndicator,
  },
  data() {
    return {
      constructionSite: null,
      queriedIssuesCount: 0,
      query: {},
      selectedIssues: [],
    }
  },
  methods: {
    deletedIssue(issue) {
      this.issues = this.issues.filter(i => i !== issue)
    },
  },
  computed: {
    exportDisabled: function () {
      return this.selectedIssues.length === 0 && this.queriedIssuesCount === 0
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
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
