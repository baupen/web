<template>
  <div id="resolve">
    <loading-indicator :spin="isLoading">
      <resolve-issues :craftsman="craftsman" :construction-site="constructionSite" />
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import DashboardConstructionSite from './components/DashboardConstructionSite'
import DashboardIssuesSummary from './components/DashboardIssuesSummary'
import DashboardFeed from './components/DashboardFeed'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import ResolveIssues from './components/ResolveIssues'

export default {
  components: {
    ResolveIssues,
    LoadingIndicator,
    DashboardFeed,
    DashboardIssuesSummary,
    DashboardConstructionSite
  },
  data () {
    return {
      craftsman: null,
      constructionSite: null
    }
  },
  computed: {
    isLoading: function () {
      return !this.craftsman || !this.constructionSite
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticateFromUrl()
        .then(me => {
          let craftsmanIri = me.craftsmanIri
          api.getById(craftsmanIri)
              .then(craftsman => {
                this.craftsman = craftsman
              })

          let constructionSiteIri = me.constructionSiteIri
          api.getById(constructionSiteIri)
              .then(constructionSite => {
                this.constructionSite = constructionSite
              })
        })
  }
}

</script>

