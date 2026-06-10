<template>
  <div id="filtered">
    <loading-indicator :spin="isLoading">
      <filtered-issues :construction-site="constructionSite" :filter="filter"/>
    </loading-indicator>
  </div>
</template>

<script>
import { apiClient, api } from './domain/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import FilteredIssues from './components/FilteredIssues'

export default {
  components: {
    FilteredIssues,
    LoadingIndicator,
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
    const me = apiClient.authenticate()
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
  }
}

</script>

<style scoped>
.min-width-600 {
  min-width: 600px;
}
</style>
