<template>
  <div id="dashboard">
    <loading-indicator :spin="!constructionSite || !constructionManagers">
      <div class="row">
        <div class="col-md-12 col-lg-4">
          <h3>{{ $t('construction_site._name') }}</h3>
          <dashboard-construction-site class="shadow" :construction-site="constructionSite"/>
        </div>
        <div class="col-md-6 col-lg-4">
          <h3>{{ $t('dashboard.activity') }}</h3>
          <dashboard-issues-graph class="shadow" :construction-site="constructionSite"/>
          <dashboard-feed class="shadow mt-4" :construction-site="constructionSite"
                          :construction-managers="constructionManagers"/>
        </div>
        <div class="col-md-6 col-lg-4">
          <h3>{{ $t('issue._plural') }}</h3>
          <dashboard-issues-summary class="shadow" :construction-site="constructionSite"/>
          <dashboard-tasks class="shadow mt-4"
                           :construction-site="constructionSite" :construction-managers="constructionManagers"
                           :construction-manager-iri="constructionManagerIri" />
          <dashboard-issues-events
              class="shadow mt-4"
              :construction-managers="constructionManagers" :construction-site="constructionSite"
              :construction-manager-iri="constructionManagerIri"/>
        </div>
      </div>
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import DashboardConstructionSite from './components/DashboardConstructionSite'
import DashboardIssuesGraph from './components/DashboardIssuesGraph'
import DashboardIssuesSummary from './components/DashboardIssuesSummary'
import DashboardFeed from './components/DashboardFeed'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import AtomSpinner from './components/Library/View/Base/AtomSpinner'
import DashboardTasks from "./components/DashboardTasks.vue";
import DashboardIssuesEvents from "./components/DashboardIssuesEvents.vue";

export default {
  components: {
    DashboardIssuesEvents,
    DashboardTasks,
    AtomSpinner,
    LoadingIndicator,
    DashboardFeed,
    DashboardIssuesGraph,
    DashboardIssuesSummary,
    DashboardConstructionSite
  },
  data() {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      constructionManagers: null,
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.authenticate()
        .then(me => {
              this.constructionManagerIri = me.constructionManagerIri
              api.getConstructionSite()
                  .then(constructionSite => this.constructionSite = constructionSite)

              api.getConstructionManagers(this.constructionSite)
                  .then(constructionManagers => this.constructionManagers = constructionManagers)
            }
        )
  }
}
</script>
