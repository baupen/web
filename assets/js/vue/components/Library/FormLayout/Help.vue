<template>
  <small class="form-text text-muted">{{ help }}</small>
</template>

<script>
export default {
  props: {
    help: {
      type: String,
      required: true
    }
  }
}
</script>
