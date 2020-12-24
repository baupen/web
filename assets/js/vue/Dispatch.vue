<template>
  <div id="dispatch">
    <loading-indicator :spin="craftsmenStatisticsLoading">
      <craftsman-table
          :craftsmen="craftsmen"
          :statistics="craftsmenStatistics"
          @selected="selectedCraftsmen = $event"/>

      <compose-craftsman-reminder-email-button
          :craftsmen="selectedCraftsmen"
          @send="sendEmail"/>
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import Feed from './components/Feed'
import CraftsmanTable from "./components/CraftsmanTable";
import LoadingIndicator from "./components/View/LoadingIndicator";
import ComposeCraftsmanReminderEmailButton from "./components/ComposeCraftsmanReminderEmailButton";

export default {
  components: {
    ComposeCraftsmanReminderEmailButton,
    LoadingIndicator,
    CraftsmanTable,
    Feed,
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
  methods: {
    sendEmail: function (email) {
      api.postRaw(email)
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
