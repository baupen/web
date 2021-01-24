<template>
  <div id="switch">
    <div class="mb-5">
      <h1>{{ $t('switch.mine') }}</h1>
      <p>{{ $t('switch.mine_help') }}</p>

      <loading-indicator :spin="isLoading">
        <construction-sites-enter-masonry
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

      <construction-sites-participation-table
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
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import AddConstructionSiteButton from './components/Action/AddConstructionSiteButton'
import ConstructionSitesParticipationTable from './components/View/ConstructionSitesParticipationTable'
import ConstructionSitesEnterMasonry from './components/View/ConstructionSitesEnterMasonry'

export default {
  components: {
    ConstructionSitesEnterMasonry,
    ConstructionSitesParticipationTable,
    AddConstructionSiteButton,
    LoadingIndicator
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
      return !this.constructionSites || !this.constructionManagers || !this.constructionManagerIri
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
      api.postConstructionSite(constructionSite, this.constructionSites, this.$t('switch.messages.success.added_construction_site'))
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
