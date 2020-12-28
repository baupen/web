<template>
  <span ref="value" :title="tooltipTitle">
    <font-awesome-icon v-if="value" :icon="['fas', icon]"/>
    <font-awesome-icon v-else :icon="['fal', icon]"/>
  </span>
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
