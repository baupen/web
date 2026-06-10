<template>
  <div id="filtered">
    <loading-indicator :spin="isLoading">
      <filtered-issues :construction-site="constructionSite" :filter="filter"/>
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
      constructionSite: null
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
