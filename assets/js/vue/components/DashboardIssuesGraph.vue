<template>
  <div class="canvas-container border p-3">
    <canvas ref="chart" class="chart" width="480" height="300"></canvas>
  </div>
</template>

<script>

import ButtonWithModalConfirm from './Library/Behaviour/ButtonWithModalConfirm'
import {api} from '../services/api'
import moment from 'moment'
import {CategoryScale, Chart, Legend, LinearScale, LineController, LineElement, PointElement, Title, Tooltip} from "chart.js";

Chart.register(LineController, LineElement, PointElement, LinearScale, Title, Legend, CategoryScale, Tooltip)
export default {
  components: {ButtonWithModalConfirm},
  data() {
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

      // if 5 issues are resolved on 01.01., then 5 is shown at 02.01., because "until then" the change happened
      // from a database point of view, this makes sense. for the user, we should adjust the labels
      const preview = moment(timeseries[0].date).subtract(1, 'day')
      labels.unshift(preview.format('DD.MM.'))
      labels.pop()

      // set max/min of graph so relevant part is visible
      const minValue = Math.min(...closed)
      const belowMinSpacer = Math.max((maxValue - minValue) * 0.3, 10) // reduce min so graph does not look empty
      const exactTargetMin = Math.max(minValue - belowMinSpacer, 0) // calculate optimal min
      const exactTargetMax = maxValue + Math.ceil((maxValue-exactTargetMin)*0.1) // add to max so graph has space to breath

      // adjust to full numbers
      const targetMin = exactTargetMin - (exactTargetMin > 1000 ? exactTargetMin % 100 : exactTargetMin % 10)
      const targetMax = exactTargetMax + (exactTargetMax > 1000 ? 1000-exactTargetMax % 100 : 10-exactTargetMax % 10)

      const ctx = this.$refs.chart.getContext('2d')
      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
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
          plugins: {
            legend: {
              reverse: true,
              onClick: null
            },
            tooltip: {}
          },
          elements: {
            point: {
              radius: 4
            }
          },
          scales: {
            x: {
              ticks: {
                maxRotation: 0,
                autoSkipPadding: 25,
                padding: 2
              }
            },
            y: {
              stacked: true,
              beginAtZero: false,
              min: targetMin,
              max: targetMax,
              ticks: {
                autoSkipPadding: 20,
                padding: 4
              }
            }
          }
        }
      })
    }
  },
  mounted() {
    api.getIssuesTimeseries(this.constructionSite)
        .then(timeseries => {
          this.draw(timeseries)
        })
  }
}
</script>

<style scoped>
.canvas-container {
  position: relative;
}

</style>
