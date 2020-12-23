<template>
  <div>
    <inline-form-field id="subject" :label="$t('email.subject')" :required="true">
      <textarea-with-feedback id="subject"
                              v-model="modelValue.subject"
                              @input="emitUpdate"
                              @valid="validProperties.subject = $event"
                              :focus="true" :inline="true" />
    </inline-form-field>

    <textarea-edit id="body"
               v-model="modelValue.body"
               @input="emitUpdate"
               @valid="validProperties.body = $event"/>
  </div>
</template>

<script>
import TextEdit from "./TextEdit";
import TextareaEdit from "./TextareaEdit";
import TextareaWithFeedback from "./TextareaWithFeedback";
import InlineFormField from "./InlineFormField";

export default {
  components: {InlineFormField, TextareaWithFeedback, TextareaEdit, TextEdit},
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
