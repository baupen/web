<template>
  <div>
    <h1>{{ $t('switch.mine') }}</h1>
    <p>{{ $t('switch.mine_help') }}</p>

    <loading-indicator-secondary :spin="isLoading">
      <template v-if="memberOfConstructionSites.length > 0">
        <div class="row">
          <div class="col-9">
            <div class="row row-cols-2 g-4">
              <div class="col" v-for="constructionSite in memberOfConstructionSites" :key="constructionSite['@id']">
                <construction-site-enter-card
                    :construction-site="constructionSite" :construction-managers="constructionManagers"/>
              </div>
            </div>
          </div>
          <div class="col-3">
            <switch-tasks
                :construction-manager-iri="constructionManagerIri"
                :construction-managers="constructionManagers"
                :construction-sites="memberOfConstructionSites" />
          </div>
        </div>
      </template>
      <div v-else class="alert alert-info">
        <template v-if="canAssociateSelf">
          {{ $t('switch.mine_none_associate_self') }}
        </template>
        <template v-else>
          {{ $t('switch.mine_none_ask_for_association') }}
        </template>
      </div>

    </loading-indicator-secondary>
  </div>
  <div v-if="canAssociateSelf" class="mt-10">
    <h2>{{ $t('switch.all') }}</h2>
    <p>{{ $t('switch.all_help') }}</p>
    <add-construction-site-button
        class="mb-2"
        :construction-manager-iri="constructionManagerIri"
        :construction-sites="showConstructionSites"
        @added="constructionSites.push($event)"
    />
    <construction-sites-participation-table
        v-if="showConstructionSites && showConstructionSites.length > 0"
        :is-loading="isLoading"
        :construction-sites="showConstructionSites"
        :construction-manager-iri="constructionManagerIri"
    />
  </div>
</template>

<script>
import ConstructionSitesParticipationTable from './View/ConstructionSitesParticipationTable'
import AddConstructionSiteButton from './Action/AddConstructionSiteButton'
import LoadingIndicator from './Library/View/LoadingIndicator'
import {addNonDuplicatesById, api} from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import ConstructionSiteEnterCard from "./View/ConstructionSiteEnterCard.vue";
import SwitchTasks from "./SwitchTasks.vue";

export default {
  components: {
    SwitchTasks,
    ConstructionSiteEnterCard,
    LoadingIndicatorSecondary,
    ConstructionSitesParticipationTable,
    AddConstructionSiteButton,
    LoadingIndicator
  },
  data() {
    return {
      constructionSites: null,
      constructionManagers: null
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionManager: {
      type: Object,
      required: true
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionSites || !this.constructionManagers
    },
    memberOfConstructionSites: function () {
      return this.orderedConstructionSites.filter(constructionSite => constructionSite.constructionManagers.includes(this.constructionManagerIri))
    },
    showConstructionSites: function () {
      if (!this.orderedConstructionSites) {
        return null
      }

      return this.orderedConstructionSites.filter(c => !c.isHidden)
    },
    orderedConstructionSites: function () {
      if (!this.constructionSites) {
        return null
      }

      return this.constructionSites
          .filter(constructionSite => !constructionSite.isDeleted)
          .sort((a, b) => a.name.localeCompare(b.name))
    },
    canAssociateSelf: function () {
      return this.constructionManager.canAssociateSelf
    }
  },
  mounted() {
    this.constructionManagers = [this.constructionManager]
    api.getConstructionSites()
        .then(constructionSites => this.constructionSites = constructionSites)
    api.getConstructionManagers()
        .then(addConstructionManagers => {
          addNonDuplicatesById(this.constructionManagers, addConstructionManagers)
        })
  }
}
</script>
