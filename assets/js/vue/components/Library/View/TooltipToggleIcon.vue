<template>
  <span ref="value" :title="tooltipTitle">
    <toggle-icon :value="value" :icon="icon" />
  </span>
</template>

<script>

import ToggleIcon from './ToggleIcon'
import {Tooltip} from "bootstrap";
export default {
  components: { ToggleIcon },
  props: {
    value: {
      type: Boolean,
      required: true
    },
    icon: {
      type: String,
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
