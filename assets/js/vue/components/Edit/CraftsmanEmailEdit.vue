<template>
  <div>
    <div class="row">
      <div class="col-2">
        {{ $t('email.to') }}
      </div>
      <div class="col">
        <p>
          {{ receivers.join(', ') }}
        </p>

        <custom-checkbox-field for-id="self-bcc" :label="$t('email.self_bcc')">
          <input
              class="custom-control-input" type="checkbox" id="self-bcc"
              v-model="email.selfBcc"
              :true-value="true"
              :false-value="false">

        </custom-checkbox-field>
      </div>
    </div>

    <hr />

    <inline-form-field for-id="subject" :label="$t('email.subject')">
      <input ref="subject" id="subject" class="form-control" type="text" required="required"
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
  </div>
</template>

<script>
import { createField, requiredRule, validateField, validateFields } from '../../services/validation'
import FormField from './Layout/FormField'
import InvalidFeedback from './Layout/InvalidFeedback'
import InlineFormField from './Layout/InlineFormField'
import CustomCheckbox from './Layout/CustomCheckboxField'
import CustomCheckboxField from './Layout/CustomCheckboxField'

export default {
  components: {
    CustomCheckboxField,
    CustomCheckbox,
    InlineFormField,
    InvalidFeedback,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      email: {
        subject: null,
        body: null,
        selfBcc: false
      },
      fields: {
        subject: createField(requiredRule()),
        body: createField(requiredRule()),
      },
    }
  },
  props: {
    receivers: {
      type: Array,
      required: true
    },
    emailTemplate: {
      type: Object,
      required: false
    },
  },
  watch: {
    isValid: function () {
      this.emitUpdate()
    },
    emailTemplate: function () {
      if (this.emailTemplate !== null) {
        this.applyEmailTemplate(this.emailTemplate)
      }
    },
  },
  computed: {
    isValid: function () {
      return this.fields.subject.errors.length === 0 &&
          this.fields.body.errors.length === 0
    }
  },
  methods: {
    emitUpdate: function () {
      this.$emit('update', this.isValid ? this.email : null)
    },
    validate: function (field) {
      validateField(this.fields[field], this.email[field])
    },
    applyEmailTemplate: function (emailTemplate) {
      this.email = {
        subject: emailTemplate.subject,
        body: emailTemplate.body,
        selfBcc: emailTemplate.selfBcc,
      }
      validateFields(this.fields, this.email)
    }
  },
  mounted () {
    validateFields(this.fields, this.email)
    this.$refs.subject.focus()

    if (this.emailTemplate !== null) {
      this.applyEmailTemplate(this.emailTemplate)
    }
  }
}
</script>
