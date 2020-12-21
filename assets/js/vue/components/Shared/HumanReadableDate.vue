<template>
    <span ref="value" data-toggle="tooltip" :title="momentDateTime.format('LL')">
      {{fromNowDayGranularity}}
    </span>
</template>

<script>

import moment from 'moment'

export default {
  props: {
    value: {
      type: String,
      required: true
    }
  },
  computed: {
    momentDateTime: function () {
      return moment(this.value);
    },
    fromNowDayGranularity: function () {
      const now = moment();
      return now < moment(this.value).add('hours', 22) ? 'today' : moment(this.value).fromNow()
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
