<template>
  <div class="row row-cols-3">
    <div class="col" v-for="constructionSite in constructionSites" :key="constructionSite['@id']">
      <construction-site-enter-card
          :construction-site="constructionSite" :construction-managers="constructionManagers"/>
    </div>
  </div>
</template>

<script>

import ConstructionSiteEnterCard from './ConstructionSiteEnterCard'

export default {
  components: {
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
