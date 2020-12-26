<template>
  <div class="invalid-feedback">
      <span v-for="error in errors">{{ $t(error) }}<br/></span>
  </div>
</template>

<script>
export default {
  props: {
    errors: {
      type: Array,
      required: true
    }
  },
}
</script>
