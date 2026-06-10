<template>
  <div id="resolve">
    <loading-indicator :spin="isLoading">
      <resolve-issues :craftsman="craftsman" :construction-site="constructionSite"/>
    </loading-indicator>
  </div>
</template>

<script>
import { api, apiClient } from './domain/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import ResolveIssues from './components/ResolveIssues'
import { meStore, store } from './domain/stores'

export default {
  components: {
    ResolveIssues,
    LoadingIndicator,
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
    const me = meStore.me
    this.constructionSite = store.constructionSite
    this.craftsman = store.craftsmen.find(craftsman => craftsman['@id'] === me.craftsmanIri)
  }
}

</script>

