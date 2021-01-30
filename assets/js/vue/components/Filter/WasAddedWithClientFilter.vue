<template>
  <boolean-filter @input="$emit('input', $event)" />
</template>
<script>
import BooleanFilter from './Base/BooleanFilter'
export default {
  emits: ["input"],
  components: { BooleanFilter }
}
</script>
