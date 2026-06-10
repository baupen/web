<template>
  <div id="dashboard">
    <loading-indicator :spin="!constructionSite || !constructionManagers || !craftsmen || !maps">
      <div class="row">
        <div class="col-md-12 col-lg-4">
          <h3>{{ $t('construction_site._name') }}</h3>
          <dashboard-construction-site class="shadow" :construction-site="constructionSite"/>
        </div>
        <div class="col-md-6 col-lg-4">
          <h3>{{ $t('dashboard.activity') }}</h3>
          <dashboard-issues-graph class="shadow" :construction-site="constructionSite"/>
          <dashboard-feed class="shadow mt-4" :construction-site="constructionSite"
                          :craftsmen="craftsmen" :maps="maps" :construction-managers="constructionManagers"
                          :construction-manager-iri="constructionManagerIri"/>
        </div>
        <div class="col-md-6 col-lg-4">
          <h3>{{ $t('issue._plural') }}</h3>
          <dashboard-issues-summary class="shadow" :construction-site="constructionSite"/>
          <dashboard-tasks class="shadow mt-4"
                           :construction-site="constructionSite" :construction-managers="constructionManagers"
                           :construction-manager-iri="constructionManagerIri"/>
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
import { api, apiClient } from './domain/api'
import DashboardConstructionSite from './components/DashboardConstructionSite'
import DashboardIssuesGraph from './components/DashboardIssuesGraph'
import DashboardIssuesSummary from './components/DashboardIssuesSummary'
import DashboardFeed from './components/DashboardFeed'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import DashboardTasks from './components/DashboardTasks.vue'
import DashboardIssuesEvents from './components/DashboardIssuesEvents.vue'
import { meStore, store } from './domain/stores'

export default {
  components: {
    DashboardIssuesEvents,
    DashboardTasks,
    LoadingIndicator,
    DashboardFeed,
    DashboardIssuesGraph,
    DashboardIssuesSummary,
    DashboardConstructionSite
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      constructionManagers: null,
      craftsmen: null,
      maps: null
    }
  },
  mounted () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionSite = store.constructionSite
    this.constructionManagers = store.constructionManagers
    this.craftsmen = store.craftsmen
    this.maps = store.maps
  }
}
</script>
