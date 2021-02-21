<template>
  <div v-for="report in reports">
    <issue-report-generation-card
        class="mt-2"
        :progressLabel="report.progressLabel" :progress="report.progress"
        :query-result-size="report.queryResultSize"
        :link="report.link" :aborted="report.aborted" />
  </div>
</template>

<script>
import { api, iriToId, maxIssuesPerReport } from '../../services/api'
import { mapTransformer } from '../../services/transformers'
import IssueReportGenerationCard from '../View/IssueReportGenerationCard'

export default {
  components: { IssueReportGenerationCard },
  emits: ['generation-finished'],
  data () {
    return {
      abortRequested: false,
      generationStatus: null,
      reports: []
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
    reportConfiguration: {
      type: Object,
      required: true
    },
    query: {
      type: Object,
      required: true
    },
    queryResultSize: {
      type: Number,
      required: true
    },
    generationRequested: {
      type: Boolean,
      required: false
    }
  },
  watch: {
    generationRequested: function () {
      if (this.generationRequested) {
        this.planGeneration()
      }
    }
  },
  computed: {
    reportQuery: function () {
      return {
        'report[withImages]': this.reportConfiguration.withImages,
        'report[tableByCraftsman]': this.reportConfiguration.tableByCraftsman,
        'report[tableByMap]': this.reportConfiguration.tableByMap,
      }
    },
  },
  methods: {
    setGenerationStatus: function (label, progress = 0) {
      this.reportGenerationStatus = {
        label,
        progress: Math.round(progress)
      }
    },
    planGeneration: function () {
      this.reportAbortRequested = false
      this.reports = []

      api.getIssuesGroup(this.constructionSite, 'map', this.query)
          .then(issuesGroupByMap => {
            const mapContainerGroups = mapTransformer.groupByIssueCount(this.maps, issuesGroupByMap, maxIssuesPerReport)
            console.log(mapContainerGroups)

            const defaultPayload = {
              progress: 0,
              progressLabel: null,
              link: null,
              aborted: false
            }

            this.reports = mapContainerGroups.map(mapContainerGroup => {
              const includedMaps = mapContainerGroup.group
                  .filter(c => c.issueCount > 0) // only include map if any issue contained
                  .map(c => c.entity)

              let currentQuery = Object.assign({}, this.query, {'map[]': includedMaps.map(m => iriToId(m['@id']))})
              const prerenderMaps = includedMaps.filter(m => m.fileUrl) // only prerender if actually file to render

              return Object.assign({
                queryResultSize: mapContainerGroup.groupIssueSum,
                query: currentQuery,
                prerenderMaps,
              }, defaultPayload)
            })

            this.startGeneration()
          })
    },
    startGeneration: function (reportIndex = 0) {
      if (this.reports.length === reportIndex) {
        this.$emit('generation-finished')
        return
      }

      this.prerenderMap(reportIndex)
    },
    prerenderMap: function (reportIndex, mapIndex = 0) {
      if (this.abortGeneration()) {
        return
      }

      const currentReport = this.reports[reportIndex]
      if (currentReport.prerenderMaps.length === mapIndex) {
        this.finishGeneration(reportIndex)
        return
      }

      currentReport.progressLabel = this.$t('actions.messages.generating_map') + ' (' + (mapIndex + 1) + '/' + currentReport.prerenderMaps.length + ')...'
      currentReport.progress = mapIndex / currentReport.prerenderMaps.length * 100

      const query = Object.assign({}, currentReport.query)
      delete query['map[]']

      let currentMap = currentReport.prerenderMaps[mapIndex]
      api.getIssuesRenderProbe(this.constructionSite, currentMap, query)
          .then(_ => {
            this.prerenderMap(reportIndex, mapIndex + 1)
          })
    },
    finishGeneration: function (reportIndex) {
      if (this.abortGeneration()) {
        return
      }

      const currentReport = this.reports[reportIndex]
      currentReport.progressLabel = this.$t('actions.messages.generating_pdf') + '...'
      currentReport.progress = 100

      api.getReportLink(this.constructionSite, this.reportQuery, currentReport.query)
          .then(link => {
            currentReport.link = link
            this.startGeneration(reportIndex + 1)
          })
    },
    abortGeneration: function () {
      if (this.generationRequested) {
        return false
      }

      this.reports.filter(r => !r.link).forEach(report => { report.aborted = true})

      return true
    },
  }
}
</script>
