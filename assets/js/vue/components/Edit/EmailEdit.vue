<template>
  <div>
    <inline-text-edit
        id="subject"
        :label="$t('email.subject')"
        v-model="modelValue.subject"
        @input="emitUpdate"
        @valid="validProperties.subject = $event"
        :focus="true" />

    <textarea-with-feedback
        id="body"
        v-model="modelValue.subject"
        @input="emitUpdate"
        @valid="validProperties.body = $event"/>
  </div>
</template>

<script>
import TextareaWithFeedback from "./Input/TextareaWithFeedback";
import InlineFormField from "./Layout/InlineFormField";
import FormField from "./Layout/FormField";
import InputWithFeedback from "./Input/InputWithFeedback";
import TextEdit from "./Widget/TextEdit";
import InlineTextEdit from "./Widget/InlineTextEdit";

export default {
  components: {InlineTextEdit, TextEdit, InputWithFeedback, FormField, InlineFormField, TextareaWithFeedback},
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      validProperties: {
        template: false,
        subject: false,
        body: false,
      }
    }
  },
  props: {
    modelValue: {
      type: Object
    }
  },
  watch: {
    isValid: function () {
      this.emitValid();
    }
  },
  methods: {
    emitUpdate: function () {
      this.$emit('update:modelValue', {...this.modelValue})
    },
    emitValid: function () {
      this.$emit('valid', this.isValid)
    }
  },
  computed: {
    isValid: function () {
      return this.validProperties.subject &&
          this.validProperties.body;
    }
  },
  mounted() {
    this.emitValid();
  }
}
</script>
