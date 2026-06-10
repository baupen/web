<template>
  <div id="switch">
    <switch-construction-sites
      :construction-manager-iri="constructionManagerIri"
      :construction-managers="constructionManagers"/>
  </div>
</template>

<script>
import SwitchConstructionSites from './components/SwitchConstructionSites'
import { meStore, switchStore } from './domain/stores'

export default {
  components: {
    SwitchConstructionSites,
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionManagers: null,
    }
  },
  beforeMount () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionManagers = switchStore.constructionManagers
  }
}

</script>
