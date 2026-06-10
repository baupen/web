<template>
  <div id="switch">
    <loading-indicator :spin="isLoading">
      <switch-construction-sites
        :construction-manager-iri="constructionManagerIri"
        :construction-managers="constructionManagers"/>
    </loading-indicator>
  </div>
</template>

<script>
import SwitchConstructionSites from './components/SwitchConstructionSites'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import { meStore, switchStore } from './domain/stores'

export default {
  components: {
    LoadingIndicator,
    SwitchConstructionSites,
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionManagers: null,
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionManagerIri || !this.constructionManager
    },
  },
  mounted () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionManagers = switchStore.constructionManagers
  }
}

</script>
