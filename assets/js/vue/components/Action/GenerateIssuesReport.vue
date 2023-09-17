<template>
  <button v-if="!reports.length" class="btn btn-primary" @click="planGeneration">
    {{ $t('_action.generate_issues_report.title') }}
  </button>
  <button v-if="reports.length && !abortRequested && !generationFinished" class="btn btn-warning"
          @click="abortRequested = true">
    {{ $t('_action.generate_issues_report.abort') }}
  </button>

  <table v-if="reports.length" class="table table-striped mt-2 mb-0">
    <tbody>
    <tr v-for="(report, index) in reports" :key="report">
      <td>
        {{ report.progressLabel }}
        <template v-if="report.link && reports.length > 1">({{ index + 1 }}/{{ reports.length }})</template>
      </td>
      <td class="w-minimal">
        <span v-if="report.link && !report.wasDownloaded">
          <a class="btn btn-primary btn-sm" target="_blank" @click="report.wasDownloaded = true"
             :href="report.link">{{ $t('_action.generate_issues_report.download') }}</a>
        </span>
        <span v-else-if="report.wasDownloaded">
          {{ $t('_action.generate_issues_report.downloaded') }}
        </span>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>
import { api, iriToId, maxIssuesPerReport } from '../../services/api'
import { mapTransformer } from '../../services/transformers'

export default {
  components: {},
  emits: ['generation-finished'],
  data () {
    return {
      abortRequested: false,
      generationFinished: false,
      reports: []
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    craftsmen: {
      type: Array,
      default: []
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
    }
  },
  computed: {
    reportQuery: function () {
      return {
        'report[withRenders]': this.reportConfiguration.withRenders,
        'report[withImages]': this.reportConfiguration.withImages,
        'report[tableByCraftsman]': this.reportConfiguration.tableByCraftsman,
        'report[tableByMap]': this.reportConfiguration.tableByMap,
        'report[groupIssuesByCraftsman]': this.reportConfiguration.groupIssuesByCraftsman,
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
      this.abortRequested = false
      this.reports = []

      api.getIssuesGroup(this.constructionSite, 'map', this.query)
          .then(issuesGroupByMap => {
            const mapContainerGroups = mapTransformer.groupByIssueCount(this.maps, issuesGroupByMap, maxIssuesPerReport)

            const defaultPayload = {
              link: null,
              wasDownloaded: false
            }

            this.reports = mapContainerGroups.map(mapContainerGroup => {
              const includedMaps = mapContainerGroup.group
                  .filter(c => c.issueCount > 0) // only include map if any issue contained
                  .map(c => c.entity)

              let currentQuery = Object.assign({}, this.query, { 'map[]': includedMaps.map(m => iriToId(m['@id'])) })
              const prerenderMaps = includedMaps.filter(m => m.fileUrl) // only prerender if actually file to render
              let queryResultSize = mapContainerGroup.groupIssueSum
              let progressLabel = this.$t('_action.generate_issues_report.pending', { issueCount: queryResultSize })

              return Object.assign({
                queryResultSize,
                query: currentQuery,
                prerenderMaps,
                progressLabel
              }, defaultPayload)
            })

            if (this.reports.length === 1) {
              this.reports[0].query = Object.assign({}, this.query)
            }

            this.startGeneration()
          })
    },
    startGeneration: function (reportIndex = 0) {
      if (this.reports.length === reportIndex) {
        this.$emit('generation-finished')
        this.generationFinished = true
        return
      }

      if (this.reportConfiguration.withRenders) {
        this.prerenderMap(reportIndex)
      } else {
        this.finishGeneration(reportIndex)
      }
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

      currentReport.progressLabel = this.$t('_action.generate_issues_report.generating_map') + ' (' + (mapIndex + 1) + '/' + currentReport.prerenderMaps.length + ')...'
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
      currentReport.progressLabel = this.$t('_action.generate_issues_report.generating_pdf') + '...'
      currentReport.progress = 100

      api.getReportLink(this.constructionSite, this.reportQuery, currentReport.query)
          .then(link => {
            currentReport.link = link
            currentReport.progressLabel = this.$t('_action.generate_issues_report.finished')
            this.startGeneration(reportIndex + 1)
          })
    },
    abortGeneration: function () {
      if (this.abortRequested) {
        this.reports = this.reports.filter(r => r.link)

        return true
      }

      return false
    },
  },
  unmounted () {
    this.abortRequested = true
  }
}
</script>
