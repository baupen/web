<template>
  <form-field for-id="trade" :label="$t('craftsman.trade')">
    <input id="trade" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.trade.dirty && !fields.trade.errors.length, 'is-invalid': fields.trade.dirty && fields.trade.errors.length }"
           @blur="fields.trade.dirty = true"
           v-model="craftsman.trade"
           @input="validate('trade')">
    <invalid-feedback :errors="fields.trade.errors" />
  </form-field>

  <form-field for-id="company" :label="$t('craftsman.company')">
    <input id="company" class="form-control" type="text" required="required"
           :class="{'is-valid': fields.company.dirty && !fields.company.errors.length, 'is-invalid': fields.company.dirty && fields.company.errors.length }"
           @blur="fields.company.dirty = true"
           v-model="craftsman.company"
           @input="validate('company')">
    <invalid-feedback :errors="fields.company.errors" />
  </form-field>

  <hr />

  <div class="form-row">
    <form-field for-id="contactName" class="col-md-6" :label="$t('craftsman.contact_name')">
      <input id="contactName" class="form-control" type="text" required="required"
             :class="{'is-valid': fields.contactName.dirty && !fields.contactName.errors.length, 'is-invalid': fields.contactName.dirty && fields.contactName.errors.length }"
             @blur="fields.contactName.dirty = true"
             v-model="craftsman.contactName"
             @input="validate('contactName')">
      <invalid-feedback :errors="fields.contactName.errors" />
    </form-field>

    <form-field for-id="email" class="col-md-6" :label="$t('craftsman.email')">
      <input id="email" class="form-control" type="text" required="required"
             :class="{'is-valid': fields.email.dirty && !fields.email.errors.length, 'is-invalid': fields.email.dirty && fields.email.errors.length }"
             @blur="fields.email.dirty = true"
             v-model="craftsman.email"
             @input="validate('email')">
      <invalid-feedback :errors="fields.email.errors" />
    </form-field>
  </div>

  <form-field for-id="emailCCs" :label="$t('craftsman.emailCCs')" :required="false">
        <textarea id="emailCCs" class="form-control"
                  :class="{'is-valid': fields.emailCCs.dirty && !fields.emailCCs.errors.length, 'is-invalid': fields.emailCCs.dirty && fields.emailCCs.errors.length }"
                  @blur="fields.emailCCs.dirty = true"
                  v-model="craftsman.emailCCs"
                  @input="validate('emailCCs')"
                  rows="3">
        </textarea>
    <help :help="$t('_form.craftsman.emailCCs_help')" />
    <invalid-feedback :errors="fields.emailCCs.errors" />
  </form-field>
</template>

<script>

import { createField, requiredRule, validateField, validateFields, changedFieldValues } from '../../services/validation'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import Help from '../Library/FormLayout/Help'

export default {
  components: {
    Help,
    InvalidFeedback,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      fields: {
        company: createField(requiredRule()),
        trade: createField(requiredRule()),
        contactName: createField(requiredRule()),
        email: createField(requiredRule()),
        emailCCs: createField(),
      },
      craftsman: {
        company: null,
        trade: null,
        contactName: null,
        email: null,
        emailCCs: null,
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
      this.setCraftsmanFromTemplate()
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.craftsman[field])
    },
    setCraftsmanFromTemplate: function () {
      if (this.templateTransformed) {
        this.craftsman = Object.assign({}, this.templateTransformed)
      }

      validateFields(this.fields, this.craftsman)
    }
  },
  computed: {
    updatePayload: function () {
      if (this.fields.company.errors.length ||
          this.fields.trade.errors.length ||
          this.fields.contactName.errors.length ||
          this.fields.email.errors.length ||
          this.fields.emailCCs.errors.length) {
        return null
      }

      const values = changedFieldValues(this.fields, this.craftsman, this.templateTransformed)
      if (Object.prototype.hasOwnProperty.call(values, 'emailCCs')) {
        values.emailCCs = values.emailCCs ? values.emailCCs.split('\n')
            .filter(e => e) : []
      }

      return values
    },
    templateTransformed: function () {
      if (!this.template) {
        return null
      }

      const emailString = this.template.emailCCs.join('\n')
      return Object.assign({}, this.template, { emailCCs: emailString })
    }
  },
  mounted () {
    this.setCraftsmanFromTemplate()
  }
}
</script>
