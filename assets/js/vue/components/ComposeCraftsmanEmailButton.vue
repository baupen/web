<template>
  <div>
    <button-with-modal-confirm modal-size="lg" :title="$t('dispatch.actions.compose_email')" :button-disabled="disabled"
                               :confirm-title="sendEmailText" :can-confirm="canConfirm" @confirm="confirm">

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

      <craftsman-email-edit
          :email-template="selectedEmailTemplate"
          :receivers="receivers"
          @update="email = $event"/>

      <p class="alert alert-info">{{ $t('dispatch.resolve_link_is_appended') }}</p>

    </button-with-modal-confirm>
  </div>
</template>

<script>

import EmailContentEdit from './Edit/CraftsmanEmailEdit'
import ButtonWithModalConfirm from './Behaviour/ButtonWithModalConfirm'
import CraftsmanEmailEdit from './Edit/CraftsmanEmailEdit'
import CustomCheckbox from './Edit/Layout/CustomCheckboxField'
import CustomCheckboxField from './Edit/Layout/CustomCheckboxField'
import FormField from './Edit/Layout/FormField'

export default {
  components: {
    FormField,
    CustomCheckboxField,
    CustomCheckbox,
    CraftsmanEmailEdit,
    ButtonWithModalConfirm,
    EmailContentEdit
  },
  emits: ['send', 'save-template', 'create-template'],
  data() {
    return {
      email: {selfBcc: false},
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
    receivers: function () {
      return this.craftsmen.map(craftsman => craftsman.contactName + ' (' + craftsman.company + ')')
    },
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
      return this.email !== null;
    }
  },
  watch: {
    proposedEmailTemplate: function () {
      this.selectedEmailTemplate = this.proposedEmailTemplate
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

      // reset state for next display
      this.selectedEmailTemplate = null
      this.saveAsTemplate = false
    },
    applyEmailTemplate: function (emailTemplate) {
      this.email = {
        subject: emailTemplate.subject,
        body: emailTemplate.body,
        selfBcc: emailTemplate.selfBcc
      }
    }
  },
  mounted() {
    this.selectedEmailTemplate = this.proposedEmailTemplate
  }
}
</script>
