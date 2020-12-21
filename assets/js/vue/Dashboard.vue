<template>
  <div id="dashboard">
    <div class="row">
      <div class="col-md-auto">
        <spinner :spin="constructionSiteSummaryLoading">
          <construction-site-summary :construction-site="constructionSite" />
        </spinner>
      </div>
      <div class="col-md-auto min-width-600">
        <spinner :spin="issuesSummaryLoading">
        <issues-summary :summary="issuesSummary" />
        </spinner>
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
import IssuesSummary from "./components/IssuesSummary";

export default {
  components: {
    IssuesSummary,
    ConstructionSiteSummary, ConstructionSiteAdd, Modal, ConstructionSiteList, ConstructionSitePreviews},
  data() {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      issuesSummary: null,
      show: false
    }
  },
  computed: {
    constructionSiteSummaryLoading: function () {
      return this.constructionSite === null;
    },
    issuesSummaryLoading: function () {
      return this.issuesSummary === null;
    }
  },
  mounted() {
    api.setupErrorNotifications(this);
    api.getMe(this);
    api.getConstructionSite(this).then(_ => {
      api.getIssuesSummary(this, this.constructionSite)
    });
  }
}

</script>

<style scoped="true">
  .min-width-600 {
    min-width: 800px;
  }
</style>
