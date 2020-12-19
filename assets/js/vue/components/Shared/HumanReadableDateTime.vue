<template>
    <span ref="value" data-toggle="tooltip" :title="momentDateTime.format('LLL')">
      {{ momentDateTime.fromNow() }}
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
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
