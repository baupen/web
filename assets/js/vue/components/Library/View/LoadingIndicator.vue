<template>
  <atom-spinner
      v-if="spin"
      :animation-duration="1000"
      :size="60"
      color="#343477"
  />
  <slot v-else />
</template>

<script>
import AtomSpinner from './Base/AtomSpinner'

export default {
  components: {
    AtomSpinner
  },
  props: {
    spin: {
      type: Boolean,
      default: true
    }
  }
}
</script>
