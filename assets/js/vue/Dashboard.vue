<template>
  <div id="dashboard">
    <loading-indicator :spin="!constructionSite">
      <div class="row">
        <div class="col-md-auto">
          <dashboard-construction-site :construction-site="constructionSite" />
          <hr />
        </div>
        <div class="col-md-auto min-width-600">
          <dashboard-issues-summary :construction-site="constructionSite" />
          <dashboard-feed class="mt-4 shadow" :construction-site="constructionSite" />
        </div>
      </div>
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import DashboardConstructionSite from './components/DashboardConstructionSite'
import DashboardIssuesSummary from './components/DashboardIssuesSummary'
import DashboardFeed from './components/DashboardFeed'
import LoadingIndicator from './components/Library/View/LoadingIndicator'

export default {
  components: {
    LoadingIndicator,
    DashboardFeed,
    DashboardIssuesSummary,
    DashboardConstructionSite
  },
  data () {
    return {
      constructionSite: null,
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticate()
        .then(_ => {
              api.getConstructionSite()
                  .then(constructionSite => { this.constructionSite = constructionSite })
            }
        )
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
