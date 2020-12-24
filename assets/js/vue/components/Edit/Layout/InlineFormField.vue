<template>
  <div class="form-group row">
    <label :for="forId" :class="'pt-2 col-sm-' + labelSize">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <div :class="'col-sm-' + (12-labelSize)">
      <slot></slot>
    </div>
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
