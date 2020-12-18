<template>
  <div>
    <masonry
        :cols="{default: 4, 1800: 6, 992: 2}"
        :gutter="{default: '10px'}"
        class="mt-2">
      <div class="grid-item" v-for="constructionSite in constructionSites" :key="constructionSite['@id']">
        <construction-site-card :construction-site="constructionSite" :construction-managers="constructionManagers" />
      </div>
    </masonry>
  </div>
</template>

<script>

import Masonry from './ConstructionSiteCards/Masonry'
import ConstructionSiteCard from "./ConstructionSiteCards/ConstructionSiteCard";

export default {
  components: {
    ConstructionSiteCard,
    Masonry
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
