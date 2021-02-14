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
