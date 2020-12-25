<template>
  <div>
    <button-with-modal-confirm modal-size="lg" :title="$t('dispatch.actions.compose_email')" :button-disabled="disabled"
                               :confirm-title="sendEmailText" :can-confirm="canConfirm" @confirm="confirm">

      <div class="row">
        <div class="col-2">
          {{ $t('email_template._name') }}
        </div>
        <div class="col">
          <select v-model="selectedEmailTemplate" @change="emailTemplateChanged" class="custom-select">
            <option :value="null">Keine Vorlage</option>
            <option :value="'new'">Neue Vorlage erstellen</option>
            <option disabled></option>
            <option v-for="emailTemplate in sortedEmailTemplates" :value="emailTemplate" :key="emailTemplate['@id']">
              {{ emailTemplate.name }}
            </option>
          </select>

          <boolean-edit v-if="selectedEmailTemplate !== null && selectedEmailTemplate !== 'new'" class="mb-0 mt-2"
                        id="save-template-changes"
                        :label="$t('dispatch.save_template_changes')"
                        v-model="saveTemplateChanges" />
        </div>
      </div>

      <hr />

      <div class="row">
        <div class="col-2">
          {{ $t('email.to') }}
        </div>
        <div class="col">
          {{ receivers.join(', ') }}

          <boolean-edit class="mb-0 mt-2"
                        id="self-bcc"
                        :label="$t('email.self_bcc')"
                        v-model="email.selfBcc" />
        </div>
      </div>

      <hr />


      <div class="invalid-feedback" v-if="dirty && !isValid">
        <span v-if="required && !localModelValue">{{ $t('validation.required') }}<br/></span>
      </div>

      <email-content-edit v-model="email" @valid="canConfirm = $event">
        <template v-slot:textarea="">
          <small class="form-text text-muted">{{ $t('dispatch.resolve_link_is_appended') }}</small>
        </template>
      </email-content-edit>
    </button-with-modal-confirm>
  </div>
</template>

<script>

import EmailContentEdit from './Edit/EmailContentEdit'
import ButtonWithModalConfirm from './Behaviour/ButtonWithModalConfirm'
import BooleanEdit from './Edit/Widget/BooleanEdit'
import InlineTextEdit from './Edit/Widget/InlineTextEdit'

export default {
  components: {
    InlineTextEdit,
    BooleanEdit,
    ButtonWithModalConfirm,
    EmailContentEdit
  },
  emits: ['send'],
  data () {
    return {
      email: { selfBcc: false },
      canConfirm: true,
      selectedEmailTemplate: null,
      saveTemplateChanges: true
    }
  },
  props: {
    craftsmen: {
      type: Array,
      required: true
    },
    emailTemplates: {
      type: Array,
      required: true
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
      return this.$tc('dispatch.actions.send_emails', this.craftsmen.length, { 'count': this.craftsmen.length })
    },
    sortedEmailTemplates: function () {
      return this.emailTemplates.sort((a, b) => a.purpose - b.purpose)
    }
  },
  methods: {
    confirm: function () {
      this.$emit('send', this.email)

      if (this.selectedEmailTemplate === 'new') {
        this.$emit('create-template', this.email)
      } else if (this.selectedEmailTemplate !== null && this.saveTemplateChanges) {
        this.$emit('save-template', this.selectedEmailTemplate, this.email)
      }
    },
    emailTemplateChanged: function () {
      if (this.selectedEmailTemplate !== null) {
        this.email.subject = this.selectedEmailTemplate.subject
        this.email.body = this.selectedEmailTemplate.body
        this.email.selfBcc = this.selectedEmailTemplate.selfBcc
      }
    }
  }
}
</script>
