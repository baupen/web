<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="constructionSiteProtocolEntries === null">
        <loading-indicator-secondary />
      </div>
      <construction-site-timeline
          v-else
          :construction-site="constructionSite"
          :construction-managers="constructionManagers" :authority-iri="constructionManagerIri"
          :protocol-entries="constructionSiteProtocolEntries"/>
    </div>
  </div>
</template>

<script>

import ConstructionSiteTimeline from "./View/ConstructionSiteTimeline.vue";
import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {api} from "../services/api";

export default {
  components: {
    LoadingIndicatorSecondary,
    ConstructionSiteTimeline,
  },
  data() {
    return {
      constructionSiteProtocolEntries: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: false
    },
  },
  mounted() {
    api.getProtocolEntries(this.constructionSite, this.constructionSite)
        .then(entries => {
          this.constructionSiteProtocolEntries = entries
        })
  }
}
</script>

<style scoped>
.limited-height {
  max-height: 30em;
}

</style>
