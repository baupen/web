<template>
  <div>
    <inline-text-edit
        id="subject"
        :label="$t('email.subject')"
        v-model="modelValue.subject"
        @input="emitUpdate"
        @valid="validProperties.subject = $event"
        :focus="true" />

    <textarea-edit
        id="body"
        v-model="modelValue.body"
        @input="emitUpdate"
        @valid="validProperties.body = $event">
      <slot name="textarea" />
    </textarea-edit>
  </div>
</template>

<script>
import TextareaWithFeedback from "./Input/TextareaWithFeedback";
import InlineFormField from "./Layout/InlineFormField";
import FormField from "./Layout/FormField";
import InputWithFeedback from "./Input/InputWithFeedback";
import TextEdit from "./Widget/TextEdit";
import InlineTextEdit from "./Widget/InlineTextEdit";
import BooleanEdit from "./Widget/BooleanEdit";
import TextareaEdit from "./Widget/TextareaEdit";

export default {
  components: {
    TextareaEdit,
    BooleanEdit,
    InlineTextEdit, TextEdit, InputWithFeedback, FormField, InlineFormField, TextareaWithFeedback},
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      validProperties: {
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
