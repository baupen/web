<template>
  <div class="canvas-container border p-3">
    <canvas ref="chart" class="chart" width="480" height="300"></canvas>
  </div>
</template>

<script>

import ButtonWithModalConfirm from './Library/Behaviour/ButtonWithModalConfirm'
import {api} from '../domain/api'
import {CategoryScale, Chart, Legend, LinearScale, LineController, LineElement, PointElement, Title, Tooltip} from "chart.js";

import { dateTimeFormatter } from '../services/formatters'

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
      let maxCurrentValue = 0
      let maxClosedValue = 0
      timeseries.forEach(entry => {
        labels.push(dateTimeFormatter.dateShort(new Date(entry.date)))
        open.push(entry.openCount)
        inspectable.push(entry.inspectableCount)
        closed.push(entry.closedCount)

        maxCurrentValue = Math.max(entry.inspectableCount + entry.closedCount, maxCurrentValue)
        maxClosedValue = Math.max(entry.closedCont, maxClosedValue)
      })

      // if 5 issues are resolved on 01.01., then 5 is shown at 02.01., because "until then" the change happened
      // from a database point of view, this makes sense. for the user, we should adjust the labels
      const preview = new Date(timeseries[0].date)
      preview.setDate(preview.getDate() - 1)
      labels.unshift(dateTimeFormatter.dateShort(preview))
      labels.pop()

      const roundMaxNumber = (number) => {
        if (number < 10) {
          return 10
        }

        if (number < 100) {
          return Math.ceil(number / 10) * 10
        }

        return Math.pow(10, Math.ceil(Math.log10(number)))
      }

      // adjust to full numbers
      const exactCurrentMax = Math.ceil(Math.max(...open, ...inspectable)*1.1) // add some space on top to have air to breath
      const currentMax = roundMaxNumber(exactCurrentMax)

      const exactClosedMax = Math.max(...closed) // add some space on top to have air to breath
      const closedMax = roundMaxNumber(exactClosedMax)

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
            yAxisID: 'y1',
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
              beginAtZero: true,
              max: currentMax,
              ticks: {
                autoSkipPadding: 20,
                padding: 4
              }
            },
            y1: {
              position: 'right',
              beginAtZero: true,
              max: closedMax,
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
