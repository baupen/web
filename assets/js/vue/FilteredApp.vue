<template>
  <div id="filtered">
    <loading-indicator :spin="isLoading">
      <filtered-issues :construction-site="constructionSite" :maps="maps" :craftsmen="craftsmen" :construction-managers="constructionManagers" :filter="filter"/>
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './domain/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import FilteredIssues from './components/FilteredIssues'
import { meStore, store } from './domain/stores'

export default {
  components: {
    FilteredIssues,
    LoadingIndicator,
  },
  data () {
    return {
      constructionSiteIri: null,
      filter: null,
      constructionSite: null,
      maps: null,
      craftsmen: null,
      constructionManagers: null,
    }
  },
  computed: {
    isLoading: function () {
      return !this.filter || !this.constructionSite
    }
  },
  mounted () {
    const me = meStore.me
    this.constructionSite = store.constructionSite
    this.craftsman = store.craftsmen.find(craftsman => craftsman['@id'] === me.craftsmanIri)
    this.maps = store.maps
    this.craftsmen = store.craftsmen
    this.constructionManagers = store.constructionManagers

    let filterIri = me.filterIri
    api.getById(filterIri)
      .then(filter => {
        this.filter = filter
      })
  }
}

</script>

<style scoped>
.min-width-600 {
  min-width: 600px;
}
</style>
