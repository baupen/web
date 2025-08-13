<template>
  <a :href="dashboardHref" class="btn btn-primary">
    {{ $t('_action.enter_construction_site.title') }}
  </a>
</template>

<script>

import {router} from "../../services/api";

export default {
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  computed: {
    dashboardHref: function () {
      return router.constructionSiteDashboard(this.constructionSite)
    }
  }
}
</script>
