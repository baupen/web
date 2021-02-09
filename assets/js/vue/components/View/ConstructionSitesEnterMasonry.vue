<template>
  <div>
    <masonry
        :cols="{default: 6, 2400: 5, 2100: 4, 1800: 3, 1200: 2, 600: 1}"
        :gutter="{default: '10px'}"
        class="mt-2">
      <div class="grid-item" v-for="constructionSite in constructionSites" :key="constructionSite['@id']">
        <construction-site-enter-card class="mb-2" :construction-site="constructionSite" :construction-managers="constructionManagers" />
      </div>
    </masonry>
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
