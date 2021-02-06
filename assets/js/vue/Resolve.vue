<template>
  <div id="resolve">
    <loading-indicator :spin="isLoading">
      <resolve-issues :craftsman-iri="craftsmanIri" :craftsman="craftsman" :construction-site="constructionSite" />
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
      craftsmanIri: null,
      constructionSiteIri: null,
      craftsman: null,
      constructionSite: null
    }
  },
  computed: {
    isLoading: function () {
      return !this.craftsmanIri || !this.craftsman || !this.constructionSite
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticateFromUrl()
        .then(me => {
          this.craftsmanIri = me.craftsmanIri
          this.constructionSiteIri = me.constructionSiteIri
          api.getById(this.craftsmanIri)
              .then(craftsman => {
                this.craftsman = craftsman
              })
          api.getById(this.constructionSiteIri)
              .then(constructionSite => {
                this.constructionSite = constructionSite
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
