<template>
  <div class="custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" :id="id" :checked="checked">
    <label class="custom-control-label" :for="id">
      <slot></slot>
    </label>
  </div>
</template>

<script>
export default {
  emits: ['clicked'],
  props: {
    id: {
      type: String,
      required: true
    },
    checked: {
      type: Boolean,
      required: true
    }
  }
}

</script>
