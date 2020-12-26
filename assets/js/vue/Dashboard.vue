<template>
  <div id="dashboard">
    <div class="row">
      <div class="col-md-auto">
        <loading-indicator :spin="constructionSiteSummaryLoading">
          <construction-site-summary :construction-site="constructionSite"/>
        </loading-indicator>
      </div>
      <div class="col-md-auto min-width-600">
        <loading-indicator :spin="issuesSummaryLoading">
          <issues-summary :issuesSummary="issuesSummary" />
        </loading-indicator>
        <loading-indicator :spin="feedLoading">
          <feed v-if="feedEntries.length > 0" class="mt-4 shadow" :entries="feedEntries"
                :construction-managers="constructionManagers" :craftsmen="craftsmen"/>
        </loading-indicator>
      </div>
    </div>
  </div>
</template>

<script>
import {api} from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import Feed from './components/Feed'
import IssuesSummary from "./components/IssuesSummary";
import LoadingIndicator from "./components/View/LoadingIndicator";

export default {
  components: {
    LoadingIndicator,
    IssuesSummary,
    Feed,
    ConstructionSiteSummary
  },
  data() {
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
    },
  },
  mounted() {
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
