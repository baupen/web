<template>
  <div id="dispatch">
    <spinner :spin="craftsmenStatisticsLoading">
      <craftsman-statistic-table :craftsmen="craftsmen" :statistics="craftsmenStatistics"
                                 @selected="this.selectedCraftsmen = $event"/>

      <email-send :craftsmen="selectedCraftsmen" />
    </spinner>
  </div>
</template>

<script>
import {api} from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import IssuesSummary from './components/IssuesSummary'
import Feed from './components/Feed'
import CraftsmanStatisticTable from "./components/CraftsmanStatisticTable";
import EmailSend from "./components/EmailSend";

export default {
  components: {
    EmailSend,
    CraftsmanStatisticTable,
    Feed,
    IssuesSummary,
    ConstructionSiteSummary
  },
  data() {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      selectedCraftsmen: [],
      craftsmen: null,
      craftsmenStatistics: null
    }
  },
  computed: {
    craftsmenStatisticsLoading: function () {
      return this.craftsmen === null || this.craftsmenStatistics === null;
    },
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite

          api.getCraftsmen(this.constructionSite, {isDeleted: false})
              .then(craftsmen => this.craftsmen = craftsmen)

          api.getCraftsmenStatistics(this.constructionSite, {isDeleted: false})
              .then(craftsmenStatistics => this.craftsmenStatistics = craftsmenStatistics)
        })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
