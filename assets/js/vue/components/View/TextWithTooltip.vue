<template>
  <p ref="value" class="d-inline-block mb-0" :title="tooltipTitle">
    <slot></slot>
  </p>
</template>

<script>

export default {
  props: {
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
