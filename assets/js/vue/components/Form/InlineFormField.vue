<template>
  <div class="form-group row">
    <label :for="id" :class="'col-form-label col-sm-' + labelSize">
      {{ label }}
      <required-indicator v-if="required" />
    </label>
    <div :class="'col-form-label col-sm-' + (12-labelSize)">
      <slot></slot>
    </div>
  </div>
</template>

<script>

import RequiredIndicator from "./RequiredIndicator";
export default {
  components: {RequiredIndicator},
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      localModelValue: "",
    }
  },
  props: {
    modelValue: null,
    id: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    labelSize: {
      type: Number,
      default: 2
    },
    required: {
      type: Boolean,
      default: false
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
