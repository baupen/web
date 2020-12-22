<template>
  <span :class="'badge ' + badgeTheme" ref="value" data-toggle="tooltip" :title="tooltipTitle">
    {{ value }}
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
    },
    colorIfNonzero: {
      type: Object,
      required: true
    }
  },
  computed: {
    badgeTheme: function (){
      return this.value ? 'badge-'+this.colorIfNonzero : ''
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
