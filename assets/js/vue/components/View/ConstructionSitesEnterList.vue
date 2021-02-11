<template>
  <div>
    <construction-site-enter-card
        class="mb-4"
        v-for="constructionSite in constructionSites" :key="constructionSite['@id']"
        :construction-site="constructionSite" :construction-managers="constructionManagers" />
  </div>
</template>

<script>

import ConstructionSiteEnterCard from './ConstructionSiteEnterCard'
import Masonry from '../Library/Behaviour/Masonry'

export default {
  components: {
    Masonry,
    ConstructionSiteEnterCard
  },
  props: {
    constructionSites: {
      type: Array,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    }
  }
}
</script>
