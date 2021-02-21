<template>
  <button v-if="!reports.length" class="btn btn-primary" @click="planGeneration">
    {{ $t('actions.generate_report') }}
  </button>
  <button v-if="reports.length && !abortRequested" class="btn btn-warning" @click="abortRequested = true">
    {{ $t('actions.abort') }}
  </button>

  <table v-if="reports.length" class="table table-striped mt-2">
    <tbody>
    <tr v-for="report in reports" :key="report">
      <td>
        {{report.progressLabel }}
      </td>
      <td class="w-minimal">
        <span v-if="report.link && !report.wasDownloaded">
          <a target="_blank" @click="report.wasDownloaded = true" :href="report.link">{{ $t("actions.download") }}</a>
        </span>
        <span v-else-if="report.wasDownloaded">
          {{ $t('actions.messages.downloaded') }}
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
  components: { },
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

              let currentQuery = Object.assign({}, this.query, {'map[]': includedMaps.map(m => iriToId(m['@id']))})
              const prerenderMaps = includedMaps.filter(m => m.fileUrl) // only prerender if actually file to render
              let queryResultSize = mapContainerGroup.groupIssueSum
              let progressLabel = this.$t("actions.messages.pending", {issueCount: queryResultSize})

              return Object.assign({
                queryResultSize,
                query: currentQuery,
                prerenderMaps,
                progressLabel
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
            currentReport.progressLabel = this.$t("actions.messages.finished")
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
  unmounted() {
    this.abortRequested = true
  }
}
</script>
