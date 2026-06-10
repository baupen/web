<template>
  <div id="register">
    <loading-indicator :spin="isLoading">
      <register-issues
          :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri"
          :initial-state-query="initialStateQuery"
      />
    </loading-indicator>
  </div>
</template>

<script>
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import RegisterIssues from './components/RegisterIssues'
import { meStore, store } from './domain/stores'

export default {
  components: {
    RegisterIssues,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
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
  }
}

</script>
