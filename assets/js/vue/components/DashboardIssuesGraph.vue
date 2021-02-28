<template>
  <div class="canvas-container">
    <canvas ref="chart" class="chart" width="1600" height="1000"></canvas>
  </div>
</template>

<script>

import Chart from 'chart.js'
import ButtonWithModalConfirm from './Library/Behaviour/ButtonWithModalConfirm'
import { api } from '../services/api'

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
        labels.push(entry.date)
        open.push(entry.openCount)
        inspectable.push(entry.inspectableCount)
        closed.push(entry.closedCount)

        maxValue = Math.max(entry.openCount + entry.inspectableCount + entry.closedCount, maxValue)
      })

      const minValue = Math.min(...closed)
      const diff = maxValue - minValue
      const targetMin = Math.max(Math.round(minValue - diff / 5), 0)

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
            reverse: true
          },
          scales: {
            yAxes: [{
              stacked: true,
              ticks: {
                min: targetMin
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
