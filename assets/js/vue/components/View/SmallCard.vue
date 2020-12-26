<template>
  <div class="card text-center">
    <div class="card-body">
      <h3>{{ title }}</h3>
      <p class="card-text">{{ description }}</p>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      required: true
    }
  },
}
</script>
