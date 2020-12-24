<template>
  <form-field>
    <checkbox :id="id" :label="label"
              v-model="localModelValue"
              @input="$emit('update:modelValue', this.localModelValue)"/>
  </form-field>
</template>

<script>
import FormField from "../Layout/FormField";
import InputWithFeedback from "../Input/InputWithFeedback";
import Checkbox from "../Input/Checkbox";

export default {
  components: {Checkbox, InputWithFeedback, FormField},
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      localModelValue: null
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
    required: {
      type: Boolean,
      default: true
    },
    focus: {
      type: Boolean,
      default: false
    }
  },
  mounted() {
    this.localModelValue = this.modelValue
  }
}
</script>
