<template>
  <div class="canvas-container border p-3">
    <canvas ref="chart" class="chart" width="480" height="300"></canvas>
  </div>
</template>

<script>

import Chart from 'chart.js'
import ButtonWithModalConfirm from './Library/Behaviour/ButtonWithModalConfirm'
import { api } from '../services/api'
import moment from 'moment'

Chart.platform.disableCSSInjection = true

export default {
  components: { ButtonWithModalConfirm },
  data () {
    return {
      chart: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    draw: function (timeseries) {
      let labels = []
      let open = []
      let inspectable = []
      let closed = []
      let maxValue = 0
      timeseries.forEach(entry => {
        labels.push(moment(entry.date).format('DD.MM.'))
        open.push(entry.openCount)
        inspectable.push(entry.inspectableCount)
        closed.push(entry.closedCount)

        maxValue = Math.max(entry.openCount + entry.inspectableCount + entry.closedCount, maxValue)
      })

      const minValue = Math.min(...closed)
      const belowMinSpacer = Math.max((maxValue - minValue) * 0.3, 10) // reduce min so graph does not look empty
      const exactTargetMin = Math.max(minValue - belowMinSpacer, 0) // calculate optimal min
      const targetMin = exactTargetMin - (exactTargetMin > 1000 ? exactTargetMin % 100 : exactTargetMin % 10) // round min

      const ctx = this.$refs.chart.getContext('2d')
      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: this.$t('issue.state.closed'),
            type: 'line',
            borderWidth: 1,
            borderColor: '#28a745',
            backgroundColor: '#bfe5c7',
            data: closed,
          }, {
            label: this.$t('issue.state.to_inspect'),
            type: 'line',
            borderWidth: 1,
            borderColor: '#ffc107',
            backgroundColor: '#ffecb5',
            data: inspectable,
          }, {
            label: this.$t('issue.state.open'),
            type: 'line',
            borderWidth: 1,
            borderColor: '#343477',
            backgroundColor: '#c2c2d6',
            data: open,
          }]
        },
        options: {
          legend: {
            reverse: true,
            onClick: null
          },
          elements: {
            point: {
              radius: 2
            }
          },
          scales: {
            xAxes: [{
              ticks: {
                maxRotation: 0,
                autoSkipPadding: 25,
                padding: 2
              }
            }],
            yAxes: [{
              type: 'linear',
              stacked: true,
              ticks: {
                beginAtZero: false,
                min: targetMin,
                autoSkipPadding: 20,
                padding: 4
              }
            }]
          }
        }
      })
    }
  },
  mounted () {
    api.getIssuesTimeseries(this.constructionSite)
        .then(timeseries => {
          this.draw(timeseries)
        })
  }
}
</script>

<style scoped="true">
.canvas-container {
  position: relative;
}

</style>
