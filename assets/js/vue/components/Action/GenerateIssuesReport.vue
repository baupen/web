<template>
  <div v-if="generationStatus">
    <p class="mb-0">{{ generationStatus.label }}</p>
    <div class="progress">
      <div class="progress-bar progress-bar-striped progress-bar-animated" :class="'progress-'+generationStatus.progress" role="progressbar"></div>
    </div>
  </div>
</template>

<script>
import { api, maxIssuesPerReport } from '../../services/api'
import { mapTransformer } from '../../services/transformers'

export default {
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
    report: {
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
        'report[withImages]': this.report.withImages,
        'report[tableByCraftsman]': this.report.tableByCraftsman,
        'report[tableByMap]': this.report.tableByMap,
      }
    },
  },
  methods: {
    setGenerationStatus: function (label, progress = 0) {
      this.reportGenerationStatus = {label, progress: Math.round(progress)}
    },
    planGeneration: function () {
      this.reportAbortRequested = false;
      this.reports = []

      api.getIssuesGroup(this.constructionSite, 'map', this.query)
          .then(mapGroups => {
            const reportGroups = mapTransformer.reportGroups(this.maps, mapGroups, maxIssuesPerReport)

            // better transform to IRI here, so transformer not connected to API!

            if (reportGroups.length === 1) {
              this.reports.push({query: this.query, mapIds: reportGroups[0]})
            } else {
              reportGroups.forEach(mapIds => {
                const subQuery = Object.assign({}, this.query, { 'maps[]': mapIds })
                this.reports.push({query: subQuery, mapIds})
              })
            }
            this.startGeneration()
          })

    },
    abortGeneration: function ()  {
      if (this.generationRequested) {
        return false
      }

      this.reports = []

      return true
    },
    startGeneration: function (reportIndex = 0) {

      this.generateMap(reportIndex)
    },
    generateMap: function (reportIndex, mapIndex = 0) {
      if (this.abortGeneration()) {
        return
      }

      const currentReport = this.reports[reportIndex];
      const query = Object.assign({}, currentReport.query, {'maps[]': currentReport.maps[mapIndex]})

      // call render URL

      if (currentReport.maps.length === mapIndex+1) {
        this.finishGeneration(reportIndex)
      }
    },
    finishGeneration: function (reportIndex) {
      if (this.abortGeneration()) {
        return
      }

      // call render URL

      if (this.reports.length > reportIndex+1) {
        this.startGeneration(reportIndex)
      }
    }
  }
}
</script>
