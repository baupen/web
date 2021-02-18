<template>
  <font-awesome-icon v-if="value" :icon="['fas', icon]" />
  <font-awesome-icon v-else :icon="['fal', icon]" />
</template>

<script>

export default {
  props: {
    value: {
      type: Boolean,
      required: true
    },
    icon: {
      type: String,
      required: true
    },
  },
}
</script>
