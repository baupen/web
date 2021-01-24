<template>
  <div class="mb-5">
    <h1>{{ $t('switch.mine') }}</h1>
    <p>{{ $t('switch.mine_help') }}</p>

    <loading-indicator-secondary :spin="isLoading">
      <construction-sites-enter-masonry
          v-if="memberOfConstructionSites.length > 0"
          :construction-sites="memberOfConstructionSites"
          :construction-managers="constructionManagers"
      />

      <div v-else class="alert alert-info">
        {{ $t('switch.messages.info.activate_construction_site') }}
      </div>
    </loading-indicator-secondary>
  </div>
  <h2>{{ $t('switch.all') }}</h2>
  <p>{{ $t('switch.all_help') }}</p>
  <add-construction-site-button
      class="mb-2"
      :disabled="isLoading"
      :construction-manager-iri="constructionManagerIri"
      :construction-sites="constructionSites"
      @added="constructionSites.push($event)"
  />
  <construction-sites-participation-table
      :is-loading="isLoading"
      :construction-sites="constructionSites"
      :construction-manager-iri="constructionManagerIri"
  />
</template>

<script>
import ConstructionSitesEnterMasonry from './View/ConstructionSitesEnterMasonry'
import ConstructionSitesParticipationTable from './View/ConstructionSitesParticipationTable'
import AddConstructionSiteButton from './Action/AddConstructionSiteButton'
import LoadingIndicator from './Library/View/LoadingIndicator'
import { api } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'

export default {
  components: {
    LoadingIndicatorSecondary,
    ConstructionSitesEnterMasonry,
    ConstructionSitesParticipationTable,
    AddConstructionSiteButton,
    LoadingIndicator
  },
  data () {
    return {
      constructionSites: null,
      constructionManagers: null
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionSites || !this.constructionManagers
    },
    memberOfConstructionSites: function () {
      return this.constructionSiteList.filter(constructionSite => constructionSite.constructionManagers.includes(this.constructionManagerIri))
    },
    constructionSiteList: function () {
      return this.constructionSites.filter(constructionSite => !constructionSite.isDeleted)
    }
  },
  mounted () {
    api.getConstructionSites()
        .then(constructionSites => this.constructionSites = constructionSites)
    api.getConstructionManagers()
        .then(constructionManagers => this.constructionManagers = constructionManagers)
  }
}
</script>
