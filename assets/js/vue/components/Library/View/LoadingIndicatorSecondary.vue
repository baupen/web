<template>
  <looping-rhombus-spinner v-if="spin" />
  <slot v-else />
</template>

<script>
import LoopingRhombusSpinner from './Base/LoopingRhombusSpinner'

export default {
  components: {
    LoopingRhombusSpinner
  },
  props: {
    spin: {
      type: Boolean,
      default: true
    }
  }
}
</script>
