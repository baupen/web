<template>
  <looping-rhombus-spinner
      v-if="spin"
      class="spinner-center"
      :animation-duration="2000"
      :size="40"
      color="#343477" />
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
