<template>
  <span v-if="value">
    {{ formatted }}
  </span>
  <span v-else>-</span>
</template>

<script>


import { dateTimeFormatter } from '../../../services/formatters'

export default {
  props: {
    value: {
      type: String,
      default: null
    }
  },
  computed: {
    formatted: function () {
      return dateTimeFormatter.dateTime(new Date(this.value));
    },
  }
}
</script>
