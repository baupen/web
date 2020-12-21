<template>
  <div id="dashboard">
    <div class="row">
      <div class="col-md-auto">
        <spinner :spin="constructionSiteSummaryLoading">
          <construction-site-summary :construction-site="constructionSite" />
        </spinner>
      </div>
      <div class="col">
        <h2>Pendenzen etc</h2>
        <h2>Feed</h2>
      </div>
    </div>
  </div>
</template>

<script>
import {api} from './services/api';
import ConstructionSitePreviews from "./components/ConstructionSitePreviews";
import ConstructionSiteList from "./components/ConstructionSiteList";
import Modal from "./components/Shared/Modal";
import ConstructionSiteAdd from "./components/ConstructionSiteAdd";
import ConstructionSiteSummary from "./components/ConstructionSiteSummary";

export default {
  components: {ConstructionSiteSummary, ConstructionSiteAdd, Modal, ConstructionSiteList, ConstructionSitePreviews},
  data() {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      show: false
    }
  },
  computed: {
    constructionSiteSummaryLoading: function () {
      return this.constructionSite === null;
    },
  },
  mounted() {
    api.setupErrorNotifications(this);
    api.loadMe(this);
    api.loadConstructionSite(this);
  }
}

</script>
