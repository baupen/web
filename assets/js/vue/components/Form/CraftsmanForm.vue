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

  <div class="row">
    <form-field for-id="contactName" class="col-md-6" :label="$t('craftsman.contact_name')">
      <input id="contactName" class="form-control" type="text" required="required"
             :class="{'is-valid': fields.contactName.dirty && !fields.contactName.errors.length, 'is-invalid': fields.contactName.dirty && fields.contactName.errors.length }"
             @blur="fields.contactName.dirty = true"
             v-model="craftsman.contactName"
             @input="validate('contactName')">
      <invalid-feedback :errors="fields.contactName.errors" />
    </form-field>

    <form-field for-id="contactJobTitle" class="col-md-6" :label="$t('craftsman.contact_job_title')" :required="false">
      <input id="contactJobTitle" class="form-control" type="text"
             :class="{'is-valid': fields.contactJobTitle.dirty && !fields.contactJobTitle.errors.length, 'is-invalid': fields.contactJobTitle.dirty && fields.contactJobTitle.errors.length }"
             @blur="fields.contactJobTitle.dirty = true"
             v-model="craftsman.contactJobTitle"
             @input="validate('contactJobTitle')">
      <invalid-feedback :errors="fields.contactJobTitle.errors" />
    </form-field>

    <form-field for-id="email" class="col-md-12" :label="$t('craftsman.email')">
      <input id="email" class="form-control" type="email" required="required"
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

  <hr/>

  <form-field for-id="telephone" :label="$t('craftsman.telephone')" :required="false">
        <textarea id="telephone" class="form-control"
                  :class="{'is-valid': fields.telephone.dirty && !fields.telephone.errors.length, 'is-invalid': fields.telephone.dirty && fields.telephone.errors.length }"
                  @blur="fields.telephone.dirty = true"
                  v-model="craftsman.telephone"
                  @input="validate('telephone')"
                  rows="3">
        </textarea>
    <invalid-feedback :errors="fields.telephone.errors" />
  </form-field>


  <form-field for-id="address" :label="$t('craftsman.address')" :required="false">
        <textarea id="address" class="form-control"
                  :class="{'is-valid': fields.address.dirty && !fields.address.errors.length, 'is-invalid': fields.address.dirty && fields.address.errors.length }"
                  @blur="fields.address.dirty = true"
                  v-model="craftsman.address"
                  @input="validate('address')"
                  rows="3">
        </textarea>
    <invalid-feedback :errors="fields.address.errors" />
  </form-field>
</template>

<script>

import {
  createField,
  requiredRule,
  validateField,
  validateFields,
  changedFieldValues,
  emailRule, emailsRule
} from '../../services/validation'
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
        contactJobTitle: createField(requiredRule()),
        email: createField(requiredRule(), emailRule()),
        emailCCs: createField(emailsRule()),

        telephone: createField(),
        address: createField(),
      },
      craftsman: {
        company: null,
        trade: null,

        contactName: null,
        contactJobTitle: null,
        email: null,
        emailCCs: null,

        telefone: null,
        address: null,
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
          this.fields.emailCCs.errors.length ||
          this.fields.telephone.errors.length ||
          this.fields.address.errors.length) {
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
    this.$emit('update', this.updatePayload)
  }
}
</script>
