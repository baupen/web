<template>
  <span v-if="value" ref="value" data-toggle="tooltip" :title="momentDateTime.format('LLL')">
    {{ momentDateTime.fromNow() }}
  </span>
  <span v-else>-</span>
</template>

<script>

import moment from 'moment'

export default {
  props: {
    value: {
      type: String,
      default: null
    }
  },
  computed: {
    momentDateTime: function () {
      return moment(this.value);
    }
  },
  mounted() {
    if (this.value) {
      $(this.$refs.value).tooltip();
    }
  }
}
</script>
