<template>
  <div id="switch">
    <h1>{{ $t("switch.mine") }}</h1>
    <p>{{ $t("switch.mine_help") }}</p>
    <spinner :spin="isLoading">
      <construction-site-cards :construction-sites="memberOfConstructionSites" :construction-managers="constructionManagers" />
    </spinner>
  </div>
</template>

<script>
import { api } from './services/api';
import ConstructionSiteCards from "./components/ConstructionSiteCards";

export default {
  components: {ConstructionSiteCards},
  data() {
    return {
      constructionManagerIri: null,
      constructionSites: null,
      constructionManagers: null
    }
  },
  computed: {
    isLoading: function () {
      return this.constructionSites === null || this.constructionManagers === null || this.constructionManagerIri === null;
    },
    memberOfConstructionSites: function() {
      return this.constructionSites.filter(constructionSite => constructionSite.constructionManagers.includes(this.constructionManagerIri))
    }
  },
  mounted() {
    api.setupErrorNotifications(this);
    api.loadMe(this);
    api.loadConstructionSites(this);
    api.loadConstructionManagers(this);
  }
}

</script>
