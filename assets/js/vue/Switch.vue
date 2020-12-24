<template>
  <div id="switch">
    <div class="mb-5">
      <h1>{{ $t('switch.mine') }}</h1>
      <p>{{ $t('switch.mine_help') }}</p>

      <loading-indicator :spin="isLoading">
        <construction-site-masonry
            v-if="memberOfConstructionSites.length > 0"
            :construction-sites="memberOfConstructionSites"
            :construction-managers="constructionManagers"
        />

        <div v-else class="alert alert-info">
          {{ $t('switch.messages.info.activate_construction_site') }}
        </div>
      </loading-indicator>
    </div>
    <h2>{{ $t('switch.all') }}</h2>
    <p>{{ $t('switch.all_help') }}</p>
    <loading-indicator :spin="isLoading">
      <add-construction-site-button
          class="mb-2"
          :construction-sites="constructionSiteList"
          @add="postConstructionSite"
      />

      <construction-site-table
          :construction-sites="constructionSiteList"
          :construction-manager-iri="constructionManagerIri"
          @remove-self="removeSelfFromConstructionSite"
          @add-self="addSelfToConstructionSite"
      />
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import AddConstructionSiteButton from './components/AddConstructionSiteButton'
import ConstructionSiteTable from './components/ConstructionSiteTable'
import ConstructionSiteMasonry from './components/ConstructionSiteMasonry'
import LoadingIndicator from './components/View/LoadingIndicator'
import Noty from 'noty'

export default {
  components: {
    LoadingIndicator,
    ConstructionSiteMasonry,
    ConstructionSiteTable,
    AddConstructionSiteButton
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSites: null,
      constructionManagers: null
    }
  },
  computed: {
    isLoading: function () {
      return this.constructionSites === null || this.constructionManagers === null || this.constructionManagerIri === null
    },
    memberOfConstructionSites: function () {
      return this.constructionSiteList.filter(constructionSite => constructionSite.constructionManagers.includes(this.constructionManagerIri))
    },
    constructionSiteList: function () {
      return this.constructionSites.filter(constructionSite => !constructionSite.isDeleted)
    }
  },
  methods: {
    postConstructionSite: function (constructionSite) {
      this.show = false
      constructionSite.constructionManagers = [this.constructionManagerIri]
      api.post('/api/construction_sites', constructionSite, this.constructionSites, this.$t('switch.messages.success.added_construction_site'))
    },
    removeSelfFromConstructionSite: function (constructionSite) {
      const constructionManagers = constructionSite.constructionManagers.filter(cm => cm !== this.constructionManagerIri)
      api.patch(constructionSite, { constructionManagers }, this.$t('switch.messages.success.removed_self'))
    },
    addSelfToConstructionSite: function (constructionSite) {
      const constructionManagers = constructionSite.constructionManagers.filter(cm => cm !== this.constructionManagerIri)
      constructionManagers.push(this.constructionManagerIri)
      api.patch(constructionSite, { constructionManagers }, this.$t('switch.messages.success.added_self'))
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSites()
        .then(constructionSites => this.constructionSites = constructionSites)
    api.getConstructionManagers()
        .then(constructionManagers => this.constructionManagers = constructionManagers)
  }
}

</script>
