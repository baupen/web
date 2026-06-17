<template>
  <div id="register">
    <loading-indicator :spin="isLoading">
      <register-issues
          :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri"
          :maps="maps" :craftsmen="craftsmen" :construction-managers="constructionManagers"
          :initial-state-query="initialStateQuery"
      />
    </loading-indicator>
  </div>
</template>

<script>
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import RegisterIssues from './components/RegisterIssues'
import { meStore, store } from './domain/stores'
import FilteredIssues from './components/FilteredIssues.vue'

export default {
  components: {
    FilteredIssues,
    RegisterIssues,
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
    },
    initialStateQuery: function () {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);

      return urlParams.has("state") ? Number(urlParams.get("state")) : null
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
