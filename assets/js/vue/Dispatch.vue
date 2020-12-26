<template>
  <div id="dispatch">
    <loading-indicator :spin="viewLoading">
      <craftsman-table
          :craftsmen="craftsmen"
          :statistics="craftsmenStatistics"
          @selected="selectedCraftsmen = $event" />

      <span class="btn-group">
        <compose-craftsman-email-button
            :disabled="unsentEmails.length > 0"
            :craftsmen="selectedCraftsmen"
            :email-templates="emailTemplates"
            @send="sendEmails"
            @create-template="createEmailTemplate"
            @save-template="saveEmailTemplate" />
        <span class="btn btn-link" v-if="unsentEmails.length > 0">{{ unsentEmails.length }}</span>
      </span>
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import Feed from './components/Feed'
import CraftsmanTable from './components/CraftsmanTable'
import LoadingIndicator from './components/View/LoadingIndicator'
import ComposeCraftsmanEmailButton from './components/ComposeCraftsmanEmailButton'
import { displaySuccess } from './services/notifiers'

export default {
  components: {
    ComposeCraftsmanEmailButton,
    LoadingIndicator,
    CraftsmanTable,
    Feed,
    ConstructionSiteSummary
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      selectedCraftsmen: [],
      craftsmen: null,
      craftsmenStatistics: null,
      unsentEmails: [],
      emailTemplates: []
    }
  },
  methods: {
    sendEmails: function (email) {
      this.unsentEmails = this.selectedCraftsmen.map(craftsman => {
        return Object.assign({ type: 4 }, email, { receiver: craftsman['@id'] })
      })

      const toBeSentEmails = [...this.unsentEmails]
      this.sendEmail(toBeSentEmails)
    },
    sendEmail (queue) {
      const email = queue.pop()
      api.postEmail(email)
          .then(_ => {
                this.unsentEmails = this.unsentEmails.filter(e => e !== email)
                const statistics = this.craftsmenStatistics.find(craftsmanStatistics => craftsmanStatistics['craftsman'] === email.receiver)
                statistics.last_email_received = (new Date()).toISOString()

                if (queue.length === 0) {
                  displaySuccess(this.$t('dispatch.messages.success.emails_sent'))
                } else {
                  this.sendEmail(queue)
                }
              }
          )
    },
    createEmailTemplate: function (email) {
      const emailTemplate = Object.assign({purpose: 1, name: email.subject, constructionSite: this.constructionSite["@id"]}, email)
      api.postEmailTemplate(emailTemplate, this.emailTemplates, this.$t('dispatch.messages.success.email_template_saved'))
    },
    saveEmailTemplate: function (emailTemplate, email) {
      let patch = email;
      if (emailTemplate.purpose === 1) {
        patch = Object.assign({ name: email.name }, patch)
      }

      api.patch(emailTemplate, patch, this.$t('dispatch.messages.success.email_template_saved'))
    },
    initializeEmailTemplates () {
      let unreadIssuesTemplate = this.emailTemplates.find(t => t.purpose === 2)
      if (!unreadIssuesTemplate) {
        this.addEmailTemplate("unread_issues", 2)
      }

      let openIssuesTemplate = this.emailTemplates.find(t => t.purpose === 3)
      if (!openIssuesTemplate) {
        this.addEmailTemplate("open_issues", 3)
      }

      let overdueIssuesTemplate = this.emailTemplates.find(t => t.purpose === 4)
      if (!overdueIssuesTemplate) {
        this.addEmailTemplate("overdue_issues", 4)
      }
    },
    addEmailTemplate(key, purpose) {
      const hi = this.$t('email_template.templates.common.hi')
      const help = this.$t('email_template.templates.common.help')

      const name = this.$t('email_template.templates.' + key + '.name', {"constructionSite": this.constructionSite.name})
      const subject = this.$t('email_template.templates.' + key + '.subject')
      const body = this.$t('email_template.templates.' + key + '.body', {"constructionSite": this.constructionSite.name})

      const template = {
        purpose,
        name,
        subject,
        body: hi + "\n\n" + body+ "\n\n" + help,
        selfBcc: false,
        constructionSite: this.constructionSite["@id"]
      }

      api.postEmailTemplate(template, this.emailTemplates)
    }
  },
  computed: {
    viewLoading: function () {
      return this.craftsmen === null || this.craftsmenStatistics === null
    },
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite

          api.getCraftsmen(this.constructionSite, { isDeleted: false })
              .then(craftsmen => this.craftsmen = craftsmen)

          api.getEmailTemplates(this.constructionSite)
              .then(emailTemplates => {
                this.emailTemplates = emailTemplates
                this.initializeEmailTemplates()
              })

          api.getCraftsmenStatistics(this.constructionSite, { isDeleted: false })
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
