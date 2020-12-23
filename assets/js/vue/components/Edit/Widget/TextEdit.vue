<template>
  <form-field :for-id="id" :label="label" :required="required">
    <input-with-feedback :id="id" type="text" :focus="focus"
                         v-model="localModelValue"
                         @input="$emit('update:modelValue', this.localModelValue)"
                         @valid="$emit('valid', $event)"
                         :required="required">
      <slot></slot>
    </input-with-feedback>
  </form-field>
</template>

<script>
import FormField from "../Layout/FormField";
import InputWithFeedback from "../Input/InputWithFeedback";

export default {
  components: {InputWithFeedback, FormField},
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
