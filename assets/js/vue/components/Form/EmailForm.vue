<template>
  <inline-form-field for-id="subject" :label="$t('email.subject')">
    <input v-focus id="subject" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.subject.dirty && !fields.subject.errors.length, 'is-invalid': fields.subject.dirty && fields.subject.errors.length }"
           @blur="fields.subject.dirty = true"
           v-model="email.subject"
           @input="validate('subject')">
    <invalid-feedback :errors="fields.subject.errors" />
  </inline-form-field>

  <form-field>
    <textarea id="body" class="form-control" required="required"
              :class="{'is-valid': fields.body.dirty && !fields.body.errors.length, 'is-invalid': fields.body.dirty && fields.body.errors.length }"
              @blur="fields.body.dirty = true"
              v-model="email.body"
              @input="validate('body')"
              rows="10">
    </textarea>
    <invalid-feedback :errors="fields.body.errors" />
  </form-field>
</template>
<script>
import InlineFormField from '../Library/FormLayout/InlineFormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import FormField from '../Library/FormLayout/FormField'
import { createField, fieldValues, requiredRule, validateField, validateFields } from '../../services/validation'

export default {
  components: {
    FormField,
    InvalidFeedback,
    InlineFormField
  },
  emits: ['update'],
  data () {
    return {
      fields: {
        subject: createField(requiredRule()),
        body: createField(requiredRule()),
      },
      email: {
        subject: null,
        body: null,
      },
    }
  },
  props: {
    template: {
      type: Object
    }
  },
  watch: {
    updatePayload: {
      deep: true,
      handler: function () {
        this.$emit('update', this.updatePayload)
      }
    },
    template: function () {
      this.setEmailFromTemplate()
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.email[field])
    },
    setEmailFromTemplate: function () {
      if (this.template) {
        this.email.subject = this.template.subject
        this.email.body = this.template.body
      }

      validateFields(this.fields, this.email)
    }
  },
  computed: {
    updatePayload: function () {
      if (this.fields.subject.errors.length ||
          this.fields.body.errors.length) {
        return null
      }

      return fieldValues(this.fields, this.email)
    },
  },
  mounted () {
    this.setEmailFromTemplate()
  }
}
</script>
