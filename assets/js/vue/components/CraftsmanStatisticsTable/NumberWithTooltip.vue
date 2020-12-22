<template>
  <span class="badge" ref="value" data-toggle="tooltip" :title="tooltipTitle">
    {{ value > 0 ? value : "-" }}
  </span>
</template>

<script>

export default {
  props: {
    value: {
      type: Object,
      required: true
    },
    tooltipTitle: {
      type: Object,
      required: true
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
