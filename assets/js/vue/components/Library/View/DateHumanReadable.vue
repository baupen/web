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
    },
    hideCurrentYear: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    showYear: function() {
      if (!this.hideCurrentYear) {
        return true
      }

      const now = new Date()
      return now.getFullYear() !== this.momentDateTime.year()
    },
    formatted: function () {
      const date = new Date(this.value)
      return this.showYear ? dateTimeFormatter.date(date) : dateTimeFormatter.dateShort(date);
    },
  }
}
</script>
