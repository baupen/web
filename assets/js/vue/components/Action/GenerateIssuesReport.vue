<template>
  <div v-for="report in reports">
    <report-generation-progress
        :label="report.label" :progress="report.progress"
        :link="report.link" :aborted="report.aborted" />
  </div>
  <div v-if="generationStatus">
    <p class="mb-0">{{ generationStatus.label }}</p>
    <div class="progress">
      <div class="progress-bar progress-bar-striped progress-bar-animated"
           :class="'progress-'+generationStatus.progress" role="progressbar"></div>
    </div>
  </div>
</template>

<script>
import { api, maxIssuesPerReport } from '../../services/api'
import { mapTransformer } from '../../services/transformers'
import ReportGenerationProgress from '../View/ReportGenerationProgress'

export default {
  components: { ReportGenerationProgress },
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
          .then(mapGroups => {
            const reportGroups = mapTransformer.reportGroups(this.maps, mapGroups, maxIssuesPerReport)

            const defaultPayload = {
              progress: 0,
              progressLabel: null,
              link: null,
              aborted: false
            }

            this.reports = reportGroups.map(maps => {
              return Object.assign({
                query: this.query,
                maps,
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

      this.generateMap(reportIndex)
    },
    generateMap: function (reportIndex, mapIndex = 0) {
      if (this.abortGeneration()) {
        return
      }

      const currentReport = this.reports[reportIndex]
      if (currentReport.maps.length === mapIndex) {
        this.finishGeneration(reportIndex)
        return
      }

      currentReport.progressLabel = this.$t('actions.messages.generating_map') + ' (' + (mapIndex + 1) + '/' + currentReport.maps.length + ')...'
      currentReport.progress = mapIndex / currentReport.maps.length * 100

      const query = Object.assign({}, currentReport.query, { 'maps[]': null })
      delete query['maps[]']

      api.getIssuesRenderProbe(this.constructionSite, currentReport.maps[mapIndex], query)
          .then(_ => {
            this.generateMap(reportIndex, mapIndex + 1)
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
