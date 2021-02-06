<template>
  <div id="register">
    <div class="btn-group mb-4">
      <export-issues-button
          :disabled="exportDisabled" :construction-site="constructionSite"
          :query="query" :queried-issue-count="queriedIssuesCount" :selected-issues="selectedIssues" />
    </div>

    <issues-table
        :construction-site="constructionSite"
        :minimal-state="2"
        @selected="selectedIssues = $event"
        @query="query = $event"
        @queried-issue-count="queriedIssuesCount = $event" />
  </div>
</template>

<script>

import ExportIssuesButton from './Action/ExportIssuesButton'
import IssuesTable from './View/IssuesTable'

export default {
  components: {
    IssuesTable,
    ExportIssuesButton
  },
  data () {
    return {
      queriedIssuesCount: 0,
      query: {},
      selectedIssues: [],
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    deletedIssue (issue) {
      this.issues = this.issues.filter(i => i !== issue)
    },
  },
  computed: {
    exportDisabled: function () {
      return this.selectedIssues.length === 0 && this.queriedIssuesCount === 0
    }
  },
}

</script>
