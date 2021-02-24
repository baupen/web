<template>
  <div id="register">
    <div class="btn-group mb-4">
      <export-issues-button
          :disabled="exportDisabled" :construction-site="constructionSite" :maps="maps"
          :query="query" :queried-issue-count="queriedIssuesCount" :selected-issues="selectedIssues" />
    </div>

    <issues-table
        view="filtered"
        :construction-site="constructionSite"
        :preset-filter="filterFromFilterEntity"
        @loaded-maps="maps = $event"
        @selected="selectedIssues = $event"
        @query="query = $event"
        @queried-issue-count="queriedIssuesCount = $event" />
  </div>
</template>

<script>

import ExportIssuesButton from './Action/ExportIssuesButton'
import IssuesTable from './View/IssuesTable'
import { filterTransformer } from '../services/transformers'

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
      maps: [],
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    filter: {
      type: Object,
      required: true
    },
  },
  methods: {
    deletedIssue (issue) {
      this.issues = this.issues.filter(i => i !== issue)
    },
  },
  computed: {
    exportDisabled: function () {
      return this.selectedIssues.length === 0 && this.queriedIssuesCount === 0
    },
    filterFromFilterEntity: function () {
      return filterTransformer.filterEntityToFilter(this.filter)
    }
  },
}

</script>
