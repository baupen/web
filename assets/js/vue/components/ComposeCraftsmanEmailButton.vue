<template>
  <div>
    <button-with-modal-confirm modal-size="lg" :title="$t('dispatch.actions.compose_email')"
                               :button-disabled="disabled" @shown="initializeForm" :can-confirm="canConfirm" :confirm-title="sendEmailText" @confirm="confirm">

      <template v-slot:secondary-footer>
        <custom-checkbox-field for-id="self-bcc" :label="$t('email.self_bcc')">
          <input
              class="custom-control-input" type="checkbox" id="self-bcc"
              v-model="this.email.selfBcc"
              :true-value="true"
              :false-value="false">
        </custom-checkbox-field>
      </template>

      <div class="row">
        <div class="col-2">
          {{ $t('email_template._name') }}
        </div>
        <div class="col">
          <form-field>
            <select v-model="selectedEmailTemplate" class="custom-select">
              <option :value="null">{{ $t('dispatch.no_template') }}</option>
              <option disabled></option>
              <option v-for="emailTemplate in sortedEmailTemplatesWithPurpose" :value="emailTemplate"
                      :key="emailTemplate['@id']">
                {{ emailTemplate.name }}
              </option>
              <option disabled v-if="sortedEmailTemplatesCustom.length > 0"></option>
              <option v-for="emailTemplate in sortedEmailTemplatesCustom" :value="emailTemplate"
                      :key="emailTemplate['@id']">
                {{ emailTemplate.name }}
              </option>
            </select>
          </form-field>

          <custom-checkbox-field for-id="save-as-template" :label="saveAsTemplateLabel">
            <input
                class="custom-control-input" type="checkbox" id="save-as-template"
                v-model="saveAsTemplate"
                :true-value="true"
                :false-value="false">
          </custom-checkbox-field>
        </div>
      </div>

      <hr/>

      <inline-form-field for-id="subject" :label="$t('email.subject')">
        <input ref="subject" id="subject" class="form-control" type="text" required="required"
               :class="{'is-valid': fields.subject.dirty && !fields.subject.errors.length, 'is-invalid': fields.subject.dirty && fields.subject.errors.length }"
               @blur="fields.subject.dirty = true"
               v-model="email.subject"
               @input="validate('subject')">
        <invalid-feedback :errors="fields.subject.errors"/>
      </inline-form-field>

      <form-field>
      <textarea id="body" class="form-control" required="required"
                :class="{'is-valid': fields.body.dirty && !fields.body.errors.length, 'is-invalid': fields.body.dirty && fields.body.errors.length }"
                @blur="fields.body.dirty = true"
                v-model="email.body"
                @input="validate('body')"
                rows="10">
      </textarea>
        <invalid-feedback :errors="fields.body.errors"/>
      </form-field>

      <p class="alert alert-info">{{ $t('dispatch.resolve_link_is_appended') }}</p>

    </button-with-modal-confirm>
  </div>
</template>

<script>

import ButtonWithModalConfirm from './Behaviour/ButtonWithModalConfirm'
import CustomCheckbox from './Edit/Layout/CustomCheckboxField'
import CustomCheckboxField from './Edit/Layout/CustomCheckboxField'
import FormField from './Edit/Layout/FormField'
import InlineFormField from "./Edit/Layout/InlineFormField";
import InvalidFeedback from "./Edit/Layout/InvalidFeedback";
import {createField, requiredRule, validateField, validateFields} from "../services/validation";

export default {
  components: {
    InvalidFeedback,
    InlineFormField,
    FormField,
    CustomCheckboxField,
    CustomCheckbox,
    ButtonWithModalConfirm,
  },
  emits: ['send', 'save-template', 'create-template'],
  data() {
    return {
      fields: {
        subject: createField(requiredRule()),
        body: createField(requiredRule()),
      },
      email: {
        subject: null,
        body: null,
        selfBcc: false
      },
      selectedEmailTemplate: null,
      saveAsTemplate: false
    }
  },
  props: {
    craftsmen: {
      type: Array,
      required: true
    },
    emailTemplates: {
      type: Array,
    },
    proposedEmailTemplate: {
      type: Object,
      required: false
    },
    disabled: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    sendEmailText: function () {
      return this.$tc('dispatch.actions.send_emails', this.craftsmen.length, {'count': this.craftsmen.length})
    },
    sortedEmailTemplatesWithPurpose: function () {
      return this.emailTemplates.filter(et => et.purpose !== null).sort((a, b) => a.purpose - b.purpose)
    },
    sortedEmailTemplatesCustom: function () {
      return this.emailTemplates.filter(et => et.purpose === null).sort((a, b) => a.name.localeCompare(a.name))
    },
    saveAsTemplateLabel: function () {
      if (this.selectedEmailTemplate === null) {
        return this.$t('dispatch.save_as_new_template')
      }
      return this.$t('dispatch.save_template_changes')
    },
    canConfirm: function () {
      return this.fields.subject.errors.length === 0 &&
          this.fields.body.errors.length === 0
    }
  },
  watch: {
    proposedEmailTemplate: function () {
      this.selectedEmailTemplate = this.proposedEmailTemplate
    },
    selectedEmailTemplate: function () {
      if (this.selectedEmailTemplate !== null) {
        this.applyEmailTemplate(this.selectedEmailTemplate)
      }
    },
    validate: function (field) {
      validateField(this.fields[field], this.email[field])
    },
  },
  methods: {
    confirm: function () {
      this.$emit('send', this.email)

      if (this.saveAsTemplate) {
        if (this.selectedEmailTemplate === null) {
          this.$emit('create-template', this.email)
        } else {
          this.$emit('save-template', this.selectedEmailTemplate, this.email)
        }
      }
    },
    applyEmailTemplate: function (emailTemplate) {
      this.email = {
        subject: emailTemplate.subject,
        body: emailTemplate.body,
        selfBcc: emailTemplate.selfBcc
      }
      validateFields(this.fields, this.email)
    },
    initializeForm: function () {
      this.$refs.subject.focus()
    }
  },
  mounted() {
    validateFields(this.fields, this.email)

    if (this.proposedEmailTemplate !== null) {
      this.selectedEmailTemplate = this.proposedEmailTemplate
      this.applyEmailTemplate(this.selectedEmailTemplate)
    }
  }
}
</script>
