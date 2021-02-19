<template>
  <span v-if="value">
    {{ momentDateTime.format('DD.MM.YYYY HH:mm') }}
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
  }
}
</script>
