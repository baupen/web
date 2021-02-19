<template>
  <span ref="value" :title="tooltipTitle">
    <toggle-icon :value="value" :icon="icon" />
  </span>
</template>

<script>

import ToggleIcon from './ToggleIcon'
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
    $(this.$refs.value).tooltip();
  }
}
</script>
