<template>
  <div class="invalid-feedback">
      <span v-for="error in errors" :key="error">{{ $t(error) }}<br/></span>
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
