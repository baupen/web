<template>
  <construction-site-view :construction-site="constructionSite" />
</template>

<script>

import ConstructionSiteView from './View/ConstructionSiteView'

export default {
  components: { ConstructionSiteView },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
}
</script>
