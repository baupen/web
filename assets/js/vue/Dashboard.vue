<template>
  <div id="dashboard">
    <div class="row">
      <div class="col-md-auto">
        <spinner :spin="constructionSiteSummaryLoading">
          <construction-site-summary :construction-site="constructionSite" />
        </spinner>
      </div>
      <div class="col-md-auto min-width-600">
        <spinner :spin="issuesSummaryLoading">
          <issues-summary :summary="issuesSummary" />
        </spinner>
        <spinner :spin="feedLoading">
          <feed class="mt-4 shadow" :entries="feedEntries" :construction-managers="constructionManagers" :craftsmen="craftsmen" />
        </spinner>
      </div>
    </div>
  </div>
</template>

<script>
import { api } from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import IssuesSummary from './components/IssuesSummary'
import Feed from './components/Feed'

export default {
  components: {
    Feed,
    IssuesSummary,
    ConstructionSiteSummary
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      constructionManagers: null,
      craftsmen: null,
      issuesSummary: null,
      feedEntries: null
    }
  },
  computed: {
    constructionSiteSummaryLoading: function () {
      return this.constructionSite === null
    },
    issuesSummaryLoading: function () {
      return this.issuesSummary === null
    },
    feedLoading: function () {
      return this.feedEntries === null || this.constructionManagers === null || this.craftsmen === null
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.getMe()
      .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionManagers()
      .then(constructionManagers => this.constructionManagers = constructionManagers);
    api.getConstructionSite()
      .then(constructionSite => {
        this.constructionSite = constructionSite

        api.getCraftsmen(this.constructionSite)
          .then(craftsmen => this.craftsmen = craftsmen)

        api.getIssuesSummary(this.constructionSite)
          .then(issuesSummary => this.issuesSummary = issuesSummary)

        api.getIssuesFeedEntries(this.constructionSite)
          .then(issuesFeedEntries => {
            this.feedEntries = issuesFeedEntries.concat(this.feedEntries ?? [])
          })
        api.getCraftsmenFeedEntries(this.constructionSite)
          .then(craftsmenFeedEntries => {
            this.feedEntries = craftsmenFeedEntries.concat(this.feedEntries ?? [])
          })
      })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
