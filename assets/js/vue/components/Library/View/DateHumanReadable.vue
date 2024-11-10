<template>
  <span v-if="value">
    {{ momentDateTime.format(showYear ? 'L' : 'DD.MM.') }}
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
    momentDateTime: function () {
      return moment(this.value);
    },
  }
}
</script>
