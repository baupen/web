<template>
  <span v-if="value">
    {{ momentDateTime.format('L') }}
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
    },
  }
}
</script>
