<template>
  <span ref="value" :title="tooltipTitle">
    <font-awesome-icon :icon="icon"/>
  </span>
</template>

<script>

import {Tooltip} from "bootstrap";

export default {
  props: {
    icon: {
      type: Array,
      required: true
    },
    tooltipTitle: {
      type: String,
      required: true
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
