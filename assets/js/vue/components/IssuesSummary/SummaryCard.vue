<template>
  <div class="card text-center">
    <div class="card-body">
      <h3>{{ number }}</h3>
      <p class="card-text">{{ description }}</p>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    number: {
      type: Object,
      required: true
    },
    description: {
      type: String,
      required: true
    }
  },
}

</script>
