<template>
  <form-field :for-id="id" :label="label" :required="required">
    <textarea-with-feedback :id="id" type="text" :focus="focus"
                         v-model="localModelValue"
                         @input="$emit('update:modelValue', this.localModelValue)"
                         @valid="$emit('valid', $event)"
                         :required="required">
      <slot></slot>
    </textarea-with-feedback>
  </form-field>
</template>

<script>
import FormField from "../Layout/FormField";
import TextareaWithFeedback from "../Input/TextareaWithFeedback";

export default {
  components: {TextareaWithFeedback, FormField},
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
