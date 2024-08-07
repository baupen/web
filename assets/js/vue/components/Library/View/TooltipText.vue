<template>
  <p ref="value" class="d-inline-block mb-0" :title="tooltipTitle">
    <slot></slot>
  </p>
</template>

<script>

import {Tooltip} from "bootstrap";

export default {
  props: {
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
