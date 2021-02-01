<template>
  <h2 class="mt-5">{{ $t('construction_site._name') }}</h2>
  <construction-site-edit-view :construction-site="constructionSite" />
</template>

<script>

import ConstructionSiteEditView from './View/ConstructionSiteEditView'

export default {
  components: {
    ConstructionSiteEditView,
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  }
}
</script>
