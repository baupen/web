<template>
  <div id="dispatch">
    <loading-indicator :spin="craftsmenStatisticsLoading">
      <craftsman-table
          :craftsmen="craftsmen"
          :statistics="craftsmenStatistics"
          @selected="selectedCraftsmen = $event" />

      <span class="btn-group">
        <compose-craftsman-email-button
            :disabled="unsentEmails.length > 0"
            :craftsmen="selectedCraftsmen"
            @send="sendEmails" />
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
      unsentEmails: []
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
      api.postRaw('/api/emails', email)
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
    }
  },
  computed: {
    craftsmenStatisticsLoading: function () {
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
