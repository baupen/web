<template>
  <span :class="'badge ' + badgeTheme" ref="value" :title="tooltipTitle">
    {{ value }}
  </span>
</template>

<script>

import { Tooltip } from 'bootstrap'

export default {
  data () {
    return {
      instance: null
    }
  },
  props: {
    value: {
      required: true
    },
    tooltipTitle: {
      type: String,
      required: true
    },
    colorIfNonzero: {
      type: String,
      required: true
    }
  },
  computed: {
    badgeTheme: function (){
      return this.value ? 'text-bg-' + this.colorIfNonzero : 'text-black'
    }
  },
  mounted() {
    this.instance = new Tooltip(this.$refs.value);
  },
  unmounted() {
    this.instance.dispose()
  }
}
</script>
