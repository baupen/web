<template>
  <form-field>
    <div class="custom-control custom-checkbox">
      <input
          type="checkbox" class="custom-control-input"
          v-model="localModelValue"
          :true-value="true"
          :false-value="false"
          @input="input"
          :id="id"
      >
      <label class="custom-control-label" :for="id">{{ label }}</label>
    </div>
  </form-field>
</template>

<script>
import FormField from "../Layout/FormField";
import InputWithFeedback from "../Input/InputWithFeedback";
import Checkbox from "../Input/Checkbox";

export default {
  components: {Checkbox, InputWithFeedback, FormField},
  emits: ['update:modelValue'],
  data() {
    return {
      localModelValue: false
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
    },
  },
  methods: {
    input: function () {
      // why do I need to invert localModalValue?
      // and why do I have to bind to @input and not @change
      this.$emit('update:modelValue', !this.localModelValue)
    }
  },
  mounted() {
    this.localModelValue = this.modelValue
  }
}
</script>
