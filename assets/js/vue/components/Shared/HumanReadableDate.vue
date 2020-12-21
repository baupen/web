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
      const today = moment().startOf('day');
      if (today <= this.momentDateTime) {
        return this.$t("view.today");
      }
      const yesterday = today.subtract(1, 'days');
      if (yesterday <= this.momentDateTime) {
        return this.$t("view.yesterday");
      }
      return moment(this.value).fromNow()
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
