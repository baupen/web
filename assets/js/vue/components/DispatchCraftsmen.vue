<template>
  <div class="btn-group mb-4">
    <compose-craftsman-email-button
        :construction-site="constructionSite" :craftsmen="selectedCraftsmen"
        @email-sent="emailSent" />
  </div>

  <craftsmen-statistics-table
      :construction-site="constructionSite" :craftsmen="craftsmen" :statistics="craftsmenStatistics"
      @selected="selectedCraftsmen = $event" />
</template>

<script>
import ComposeCraftsmanEmailButton from './Action/ComposeCraftsmanEmailButton'
import CraftsmenStatisticsTable from './View/CraftsmenStatisticsTable'
import { api } from '../services/api'

export default {
  components: {
    CraftsmenStatisticsTable,
    ComposeCraftsmanEmailButton
  },
  data () {
    return {
      craftsmen: null,
      craftsmenStatistics: null,
      selectedCraftsmen: [],
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    emailSent: function (craftsman) {
      const statistics = this.craftsmenStatistics.find(craftsmanStatistics => craftsmanStatistics['craftsman'] === craftsman['@id'])
      statistics.lastEmailReceived = (new Date()).toISOString()
    }
  },
  mounted () {
    api.getCraftsmen(this.constructionSite, { isDeleted: false })
        .then(craftsmen => this.craftsmen = craftsmen)

    api.getCraftsmenStatistics(this.constructionSite, { isDeleted: false })
        .then(craftsmenStatistics => this.craftsmenStatistics = craftsmenStatistics)
  }
}
</script>
