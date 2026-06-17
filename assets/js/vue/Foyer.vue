<template>
  <div id="foyer">
    <loading-indicator :spin="isLoading">
      <foyer-issues :construction-site="constructionSite" :maps="maps" :craftsmen="craftsmen" :construction-managers="constructionManagers" :construction-manager-iri="constructionManagerIri"/>
    </loading-indicator>
  </div>
</template>

<script>
import { api, apiClient } from './domain/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import FoyerIssues from './components/FoyerIssues'
import { meStore, store } from './domain/stores'
import FilteredIssues from './components/FilteredIssues.vue'

export default {
  components: {
    FilteredIssues,
    FoyerIssues,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      maps: null,
      craftsmen: null,
      constructionManagers: null,
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionSite || !this.constructionManagerIri
    }
  },
  mounted () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionSite = store.constructionSite
    this.maps = store.maps
    this.craftsmen = store.craftsmen
    this.constructionManagers = store.constructionManagers
  }
}

</script>
