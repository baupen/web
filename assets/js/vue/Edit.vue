<template>
  <div id="edit">
    <loading-indicator :spin="loading">
      <edit-construction-site :construction-site="constructionSite"/>
      <edit-maps :construction-site="constructionSite"/>
      <edit-craftsmen :construction-site="constructionSite"/>
      <edit-construction-managers :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri"/>
    </loading-indicator>
  </div>
</template>

<script>
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import EditCraftsmen from './components/EditCraftsmen'
import EditMaps from './components/EditMaps'
import EditConstructionSite from './components/EditConstructionSite'
import EditConstructionManagers from './components/EditConstructionManagers'
import { meStore, store } from './domain/stores'

export default {
  components: {
    EditConstructionManagers,
    EditConstructionSite,
    EditMaps,
    EditCraftsmen,
    LoadingIndicator,
  },
  data () {
    return {
      constructionSite: null,
      constructionManagerIri: null,
    }
  },
  computed: {
    loading: function () {
      return !this.constructionSite || !this.constructionManager
    }
  },
  mounted () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionSite = store.constructionSite
  }
}

</script>
