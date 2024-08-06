<template>
  <span :class="'badge ' + badgeTheme" ref="value" :title="tooltipTitle">
    {{ value }}
  </span>
</template>

<script>

export default {
  props: {
    value: {
      required: true
    },
    tooltipTitle: {
      type: String,
      required: true
    },
    colorIfNonzero: {
      type: String,
      required: true
    }
  },
  computed: {
    badgeTheme: function (){
      return this.value ? 'text-bg-' + this.colorIfNonzero : 'text-black'
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
