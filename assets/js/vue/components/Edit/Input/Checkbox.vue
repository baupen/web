<template>
  <div class="custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" :id="id"
           v-model="modelValue" :value="value" @change="$emit('update:modelValue', modelValue)">
    <label class="custom-control-label" :for="id">{{ label }}</label>
  </div>
</template>

<script>
export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: {
      required: true
    },
    value: {
      required: true
    },
    label: {
      default: ''
    },
    id: {
      type: String,
      required: true
    }
  }
}

</script>
