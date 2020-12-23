<template>
  <span :class="'badge ' + badgeTheme" ref="value" data-toggle="tooltip" :title="tooltipTitle">
    {{ value }}
  </span>
</template>

<script>

export default {
  props: {
    value: {
      type: Number,
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
      return this.value ? 'badge-'+this.colorIfNonzero : ''
    }
  },
  mounted() {
    $(this.$refs.value).tooltip();
  }
}
</script>
