<template>
  <span ref="value">
    <font-awesome-icon :icon="icon"/>
  </span>
</template>

<script>

export default {
  props: {
    icon: {
      type: Array,
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
