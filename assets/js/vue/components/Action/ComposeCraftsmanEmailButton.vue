<template>
  <span class="btn btn-link" v-if="unsentEmails.length > 0">{{ unsentEmails.length }}</span>
  <button-with-modal-confirm
      modal-size="lg" :title="$t('_action.compose_craftsman_email.title')"
      :button-disabled="disabled" :can-confirm="canConfirm" :confirm-title="sendEmailText"
      @confirm="confirm">

    <template v-slot:secondary-footer>
      <button class="btn btn-outline-secondary me-auto" @click="saveEmailTemplate" :disabled="storingTemplate">
        {{ $t('_action.compose_craftsman_email.save_as_template') }}
      </button>
      <custom-checkbox for-id="self-bcc" :label="$t('email.self_bcc')">
        <input
            class="form-check-input" type="checkbox" id="self-bcc"
            v-model="selfBcc"
            :true-value="true"
            :false-value="false">
      </custom-checkbox>
    </template>

    <div class="row">
      <div class="col-2">
        {{ $t('email.templates._name') }}
      </div>
      <div class="col">
        <loading-indicator-secondary v-if="emailTemplatesLoading" />
        <form-field v-else>
          <select v-model="selectedEmailTemplate" class="form-select">
            <option :value="null">{{ $t('_action.compose_craftsman_email.no_template') }}</option>
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
      </div>
    </div>

    <hr />

    <email-form :template="selectedEmailTemplate" @update="email = $event" />

    <p class="alert alert-info mb-0">{{ $t('_action.compose_craftsman_email.resolve_link_is_appended') }}</p>

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import { displaySuccess } from '../../services/notifiers'
import EmailForm from '../Form/EmailForm'
import LoadingIndicatorSecondary from '../Library/View/LoadingIndicatorSecondary'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import FormField from '../Library/FormLayout/FormField'
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";

export default {
  components: {
    CustomCheckbox,
    FormField,
    CustomCheckboxField,
    ButtonWithModalConfirm,
    LoadingIndicatorSecondary,
    EmailForm,
  },
  emits: ['email-sent'],
  data () {
    return {
      selfBcc: false,
      email: null,
      emailTemplates: null,
      selectedEmailTemplate: null,
      unsentEmails: [],
      storingTemplate: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    defaultPurpose: {
      type: Number,
      default: 1 // issues open template
    },
    type: {
      type: Number,
      default: 4 // craftsman issue reminder
    },
  },
  computed: {
    disabled: function () {
      return this.unsentEmails.length > 0 || this.craftsmen.length === 0
    },
    emailTemplatesLoading: function () {
      return !this.emailTemplates || this.emailTemplates.length < 3
    },
    sendEmailText: function () {
      return this.$tc('_action.compose_craftsman_email.send_emails', this.craftsmen.length, { 'count': this.craftsmen.length })
    },
    proposedEmailTemplate: function () {
      if (!this.emailTemplates) {
        return null
      }

      return this.emailTemplates.find(t => t.purpose === this.defaultPurpose)
    },
    sortedEmailTemplatesWithPurpose: function () {
      return this.emailTemplates.filter(et => et.purpose)
          .sort((a, b) => a.purpose - b.purpose)
    },
    sortedEmailTemplatesCustom: function () {
      return this.emailTemplates.filter(et => !et.purpose)
          .sort((a, b) => a.name.localeCompare(b.name))
    },
    canConfirm: function () {
      return !!this.email
    }
  },
  watch: {
    proposedEmailTemplate: function () {
      this.selectedEmailTemplate = this.proposedEmailTemplate
    },
    selectedEmailTemplate: function () {
      if (this.selectedEmailTemplate) {
         this.selfBcc = this.selectedEmailTemplate.selfBcc
      }
    }
  },
  methods: {
    confirm: function () {
      this.sendEmails()
    },
    sendEmails: function () {
      this.unsentEmails = this.craftsmen.map(craftsman => {
        return Object.assign({ type: this.type, selfBcc: this.selfBcc }, this.email, { receiver: craftsman['@id'] })
      })

      this.processUnsentEmails()
    },
    processUnsentEmails () {
      const email = this.unsentEmails[0]
      api.postEmail(email)
          .then(_ => {
                this.unsentEmails.shift()
                this.$emit('email-sent', this.craftsmen.find(c => c['@id'] === email.receiver))

                if (this.unsentEmails.length === 0) {
                  displaySuccess(this.$t('_action.compose_craftsman_email.sent_emails'))
                } else {
                  this.processUnsentEmails()
                }
              }
          )
    },
    saveEmailTemplate: function () {
      this.storingTemplate = true

      if (this.selectedEmailTemplate) {
        let patch = Object.assign({selfBcc: this.selfBcc}, this.email)
        if (!this.selectedEmailTemplate.purpose) {
          patch.name = this.email.subject
        }

        api.patch(this.selectedEmailTemplate, patch, this.$t('_action.compose_craftsman_email.saved_email_template'))
            .then(_ => this.storingTemplate = false)
      } else {
        const emailTemplate = Object.assign({
          selfBcc: this.selfBcc,
          name: this.email.subject,
          constructionSite: this.constructionSite['@id']
        }, this.email)

        api.postEmailTemplate(emailTemplate, this.emailTemplates, this.$t('_action.compose_craftsman_email.saved_email_template'))
            .then(emailTemplate => {
              this.storingTemplate = false
              this.selectedEmailTemplate = emailTemplate
            })
      }
    },
    initializeDefaultEmailTemplates () {
      let openIssuesTemplate = this.emailTemplates.find(t => t.purpose === 1)
      if (!openIssuesTemplate) {
        this.addDefaultEmailTemplate('open_issues', 1)
      }

      let unreadIssuesTemplate = this.emailTemplates.find(t => t.purpose === 2)
      if (!unreadIssuesTemplate) {
        this.addDefaultEmailTemplate('unread_issues', 2)
      }

      let overdueIssuesTemplate = this.emailTemplates.find(t => t.purpose === 3)
      if (!overdueIssuesTemplate) {
        this.addDefaultEmailTemplate('overdue_issues', 3)
      }
    },
    addDefaultEmailTemplate (key, purpose) {
      const hi = this.$t('email.templates._common.hi')
      const help = this.$t('email.templates._common.help')

      const name = this.$t('email.templates.' + key + '.name')
      const subject = this.$t('email.templates.' + key + '.subject', { 'constructionSite': this.constructionSite.name })
      const body = this.$t('email.templates.' + key + '.body', { 'constructionSite': this.constructionSite.name })

      const template = {
        purpose,
        name,
        subject,
        body: hi + '\n\n' + body + '\n\n' + help,
        selfBcc: false,
        constructionSite: this.constructionSite['@id']
      }

      api.postEmailTemplate(template, this.emailTemplates)
    },
  },
  mounted () {
    api.getEmailTemplates(this.constructionSite)
        .then(emailTemplates => {
          this.emailTemplates = emailTemplates
          this.initializeDefaultEmailTemplates()
        })

  }
}
</script>
