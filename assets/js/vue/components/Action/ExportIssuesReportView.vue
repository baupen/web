<template>
  <h3 v-if="showTitle">{{ $t('_action.export_issues_report.title') }}</h3>
  <p class="alert alert-info">
    {{ $t('_action.export_issues_report.help') }}
  </p>

  <issue-report-form v-if="allowReportConfiguration" :template="report" @update="report = $event" />

  <generate-issues-report
      :construction-site="constructionSite" :maps="maps" :report-configuration="report"
      :query="query" :query-result-size="queryResultSize"
  />
</template>

<script>

import IssueReportForm from '../Form/IssueReportForm'
import GenerateIssuesReport from './GenerateIssuesReport'

export default {
  components: {
    GenerateIssuesReport,
    IssueReportForm,
  },
  data () {
    return {
      report: {
        withRenders: true,
        withImages: true,
        tableByCraftsman: true,
        tableByMap: false
      },
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      default: []
    },
    query: {
      type: Object,
      required: true
    },
    queryResultSize: {
      type: Number,
      required: true
    },
    defaultReportConfiguration: {
      type: Object,
      default: null
    },
    allowReportConfiguration: {
      type: Boolean,
      default: true
    },
    showTitle: {
      type: Boolean,
      default: true
    }
  },
  mounted () {
    if (this.defaultReportConfiguration) {
      this.reportConfiguration = this.defaultReportConfiguration
    }
  }
}
</script>
