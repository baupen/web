<template>
  <atom-spinner v-if="spin" />
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
