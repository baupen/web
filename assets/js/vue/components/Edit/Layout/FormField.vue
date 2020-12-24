<template>
  <div class="form-group">
    <label v-if="label" :for="forId">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <slot></slot>
  </div>
</template>

<script>

export default {
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      localModelValue: "",
    }
  },
  props: {
    modelValue: null,
    forId: {
      type: String,
    },
    label: {
      type: String,
    },
    required: {
      type: Boolean,
      default: true
    },
  },
  computed: {
    classNames: function () {
      let classNames = []

      if (this.size !== 0) {
        classNames.push('col-md-' + this.size)
      }

      if (this.inline) {
        classNames.push('row')
      }

      return classNames.join(" ")
    },
  },
  methods: {
    emitUpdate() {
      this.$emit('update:modelValue', this.localModelValue)
    },
  },
  mounted() {
    this.localModelValue = this.modelValue;
  }
}
</script>
