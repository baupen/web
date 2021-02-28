<template>
  <div class="canvas-container">
    <canvas ref="chart" class="chart" width="1600" height="1000"></canvas>
  </div>
</template>

<script>

import Chart from 'chart.js'
import ButtonWithModalConfirm from './Library/Behaviour/ButtonWithModalConfirm'

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
  computed: {},
  mounted () {
    const exampleData = [
      {
        date: '2020-01-31',
        open: 200,
        inspectable: 10,
        closed: 12032
      },
      {
        date: '2020-02-01',
        open: 129,
        inspectable: 80,
        closed: 12132
      },
      {
        date: '2020-02-02',
        open: 80,
        inspectable: 71,
        closed: 12142
      },
      {
        date: '2020-02-03',
        open: 150,
        inspectable: 21,
        closed: 12202
      },
      {
        date: '2020-02-04',
        open: 240,
        inspectable: 37,
        closed: 12289
      },
      {
        date: '2020-02-05',
        open: 170,
        inspectable: 142,
        closed: 12364
      }
    ]

    let labels = []
    let open = []
    let inspectable = []
    let closed = []
    let maxValue = 0;
    exampleData.forEach(entry => {
      labels.push(entry.date)
      open.push(entry.open)
      inspectable.push(entry.inspectable)
      closed.push(entry.closed)

      maxValue = Math.max(entry.open + entry.inspectable + entry.closed, maxValue)
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
          label: 'closed',
          type: 'line',
          borderWidth: 1,
          borderColor: '#28a745',
          backgroundColor: '#bfe5c7',
          data: closed,
        }, {
          label: 'to inspect',
          type: 'line',
          borderWidth: 1,
          borderColor: '#ffc107',
          backgroundColor: '#ffecb5',
          data: inspectable,
        },{
          label: 'open',
          type: 'line',
          borderWidth: 1,
          borderColor: '#343477',
          backgroundColor: '#c2c2d6',
          data: open,
        } ]
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
}
</script>

<style scoped="true">
.canvas-container {
  position: relative;
}

</style>
