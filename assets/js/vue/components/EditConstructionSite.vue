<template>
  <h2 class="mt-5">{{ $t('construction_site._name') }}</h2>
  <construction-site-edit-view :construction-site="constructionSite" :construction-sites="constructionSites" />
</template>

<script>

import { api } from '../services/api'
import ConstructionSiteEditView from './View/ConstructionSiteEditView'

export default {
  components: {
    ConstructionSiteEditView,
  },
  data() {
    return {
      constructionSites: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  mounted () {
    api.getConstructionSites(this.constructionSite)
        .then(constructionSites =>  this.constructionSites = constructionSites)
  }
}
</script>
