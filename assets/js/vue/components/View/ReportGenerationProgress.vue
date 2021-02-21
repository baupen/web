<template>

</template>

<script>
export default {
  props: {
    label: {
      type: String,
      required: false
    },
    progress: {
      type: Number,
      required: true
    },
    link: {
      type: String,
      required: false
    },
    aborted: {
      type: Boolean,
      required: false
    }
  }
}
</script>
