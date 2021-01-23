<template>
  <div id="dispatch">
    <div class="btn-group mb-4">
      <compose-craftsman-email-button
          :disabled="unsentEmails.length > 0 || selectedCraftsmen.length === 0 || emailTemplatesLoading"
          :craftsmen="selectedCraftsmen"
          :email-templates="emailTemplates"
          :proposed-email-template="proposedEmailTemplate"
          @send="sendEmails"
          @create-template="createEmailTemplate"
          @save-template="saveEmailTemplate"/>
      <span class="btn btn-link" v-if="unsentEmails.length > 0">{{ unsentEmails.length }}</span>
    </div>

    <loading-indicator :spin="craftsmenLoading">
      <craftsman-table
          :craftsmen="craftsmen"
          :statistics="craftsmenStatistics"
          @selected="selectedCraftsmen = $event"/>
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import Feed from './components/Feed'
import CraftsmanTable from './components/CraftsmanTable'
import LoadingIndicator from './components/View/LoadingIndicator'
import ComposeCraftsmanEmailButton from './components/ComposeCraftsmanEmailButton'
import {displaySuccess} from './services/notifiers'

export default {
  components: {
    ComposeCraftsmanEmailButton,
    LoadingIndicator,
    CraftsmanTable,
    Feed,
    ConstructionSiteSummary
  },
  data() {
    return {
      constructionSite: null,
      craftsmen: null,
      craftsmenStatistics: null,
      emailTemplates: null,
      selectedCraftsmen: [],
      unsentEmails: [],
    }
  },
  methods: {
    sendEmails: function (email) {
      this.unsentEmails = this.selectedCraftsmen.map(craftsman => {
        return Object.assign({type: 4}, email, {receiver: craftsman['@id']})
      })

      this.processUnsentEmails()
    },
    processUnsentEmails() {
      const email = this.unsentEmails[0]
      api.postEmail(email)
          .then(_ => {
                this.unsentEmails.shift()
                const statistics = this.craftsmenStatistics.find(craftsmanStatistics => craftsmanStatistics['craftsman'] === email.receiver)
                statistics.lastEmailReceived = (new Date()).toISOString()

                if (this.unsentEmails.length === 0) {
                  displaySuccess(this.$t('dispatch.messages.success.emails_sent'))
                } else {
                  this.processUnsentEmails()
                }
              }
          )
    },
    createEmailTemplate: function (email) {
      const emailTemplate = Object.assign({
        name: email.subject,
        constructionSite: this.constructionSite["@id"]
      }, email)
      api.postEmailTemplate(emailTemplate, this.emailTemplates, this.$t('dispatch.messages.success.email_template_saved'))
    },
    saveEmailTemplate: function (emailTemplate, email) {
      let patch = email;
      if (!emailTemplate.purpose) {
        patch = Object.assign({name: email.subject}, patch)
      }

      api.patch(emailTemplate, patch, this.$t('dispatch.messages.success.email_template_saved'))
    },
    initializeEmailTemplates() {
      let openIssuesTemplate = this.emailTemplates.find(t => t.purpose === 1)
      if (!openIssuesTemplate) {
        this.addEmailTemplate("open_issues", 1)
      }

      let unreadIssuesTemplate = this.emailTemplates.find(t => t.purpose === 2)
      if (!unreadIssuesTemplate) {
        this.addEmailTemplate("unread_issues", 2)
      }

      let overdueIssuesTemplate = this.emailTemplates.find(t => t.purpose === 3)
      if (!overdueIssuesTemplate) {
        this.addEmailTemplate("overdue_issues", 3)
      }
    },
    addEmailTemplate(key, purpose) {
      const hi = this.$t('email_template.templates.common.hi')
      const help = this.$t('email_template.templates.common.help')

      const name = this.$t('email_template.templates.' + key + '.name')
      const subject = this.$t('email_template.templates.' + key + '.subject', {"constructionSite": this.constructionSite.name})
      const body = this.$t('email_template.templates.' + key + '.body', {"constructionSite": this.constructionSite.name})

      const template = {
        purpose,
        name,
        subject,
        body: hi + "\n\n" + body + "\n\n" + help,
        selfBcc: false,
        constructionSite: this.constructionSite["@id"]
      }

      api.postEmailTemplate(template, this.emailTemplates)
    }
  },
  computed: {
    craftsmenLoading: function () {
      return !this.craftsmen || !this.craftsmenStatistics
    },
    emailTemplatesLoading: function () {
      return !this.emailTemplates || this.emailTemplates.length < 3
    },
    proposedEmailTemplate: function () {
      if (!this.emailTemplates) {
        return null;
      }

      return this.emailTemplates.find(t => t.purpose === 1);
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite

          api.getCraftsmen(this.constructionSite, {isDeleted: false})
              .then(craftsmen => this.craftsmen = craftsmen)

          api.getEmailTemplates(this.constructionSite)
              .then(emailTemplates => {
                this.emailTemplates = emailTemplates
                this.initializeEmailTemplates()
              })

          api.getCraftsmenStatistics(this.constructionSite, {isDeleted: false})
              .then(craftsmenStatistics => this.craftsmenStatistics = craftsmenStatistics)
        })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
