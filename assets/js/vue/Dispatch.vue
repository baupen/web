<template>
  <div id="dispatch">
    <loading-indicator :spin="!constructionSite || !constructionManagers">
      <dispatch-craftsmen :construction-manager-iri="constructionManagerIri" :construction-site="constructionSite"
                          :construction-managers="constructionManagers"/>
    </loading-indicator>
  </div>
</template>

<script>
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import DispatchCraftsmen from './components/DispatchCraftsmen'
import { meStore, store } from './domain/stores'

export default {
  components: {
    DispatchCraftsmen,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      constructionManagers: null
    }
  },
  mounted () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionSite = store.constructionSite
    this.constructionManagers = store.constructionManagers
  }
}

</script>

<style scoped>
.min-width-600 {
  min-width: 600px;
}
</style>
