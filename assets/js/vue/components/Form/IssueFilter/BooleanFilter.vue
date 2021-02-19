<template>
</template>


<script>

import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
export default {
  components: {
    CustomCheckboxField

  },
  emits: ['input'],
  data () {
    return {
      value: false
    }
  },
  props: {
    default: {
      default: null
    }
  },
  watch: {
    value: function () {
      this.$emit('input', this.value)
    },
  },
  mounted () {
    this.value = this.default
  }
}
</script>
