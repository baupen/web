<template>
  <div id="edit">
    <h2 class="mt-5">{{ $t('craftsman._plural') }}</h2>
    <p>{{ $t('edit.craftsmen_help') }}</p>
    <loading-indicator :spin="!constructionSite">
      <add-craftsman-button :construction-site="constructionSite" />
      <edit-craftsmen-table class="mt-2" :construction-site="constructionSite" />
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import ConstructionSiteSummary from './components/ConstructionSiteSummary'
import Feed from './components/Feed'
import IssuesSummary from "./components/IssuesSummary";
import LoadingIndicator from "./components/View/LoadingIndicator";
import CraftsmanTable from './components/CraftsmanTable'
import EditCraftsmenTable from './components/EditCraftsmenTable'
import AddCraftsmanButton from './components/Action/AddCraftsmanButton'

export default {
  components: {
    AddCraftsmanButton,
    EditCraftsmenTable,
    CraftsmanTable,
    LoadingIndicator,
  },
  data() {
    return {
      constructionSite: null,
    }
  },
  computed: {
    constructionSiteSummaryLoading: function () {
      return !this.constructionSite
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite
        })
  }
}

</script>

<style scoped="true">
.min-width-600 {
  min-width: 600px;
}
</style>
