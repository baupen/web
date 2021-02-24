<template>
  <div id="filtered">
    <loading-indicator :spin="isLoading">
      <filtered-issues :construction-site="constructionSite" :filter="filter" />
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
import FilteredIssues from './components/FilteredIssues'

export default {
  components: {
    FilteredIssues,
    ResolveIssues,
    LoadingIndicator,
    DashboardFeed,
    DashboardIssuesSummary,
    DashboardConstructionSite
  },
  data () {
    return {
      constructionSiteIri: null,
      filter: null,
      constructionSite: null
    }
  },
  computed: {
    isLoading: function () {
      return !this.filter || !this.constructionSite
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticateFromUrl()
        .then(me => {
          let filterIri = me.filterIri
          api.getById(filterIri)
              .then(filter => {
                this.filter = filter
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

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
