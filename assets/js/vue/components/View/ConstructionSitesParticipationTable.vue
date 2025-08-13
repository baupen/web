<template>
  <div>
    <div class="mb-2" v-if="constructionSites.length >= 10">
      <input id="name" v-model="constructionSiteFilter" class="form-control mw-25" type="text"
             :placeholder="$t('_view.construction_sites_participation_table.search')">
    </div>
    <table class="table table-hover table-striped border shadow">
      <thead>
      <tr>
        <th class="w-thumbnail"></th>
        <th>{{ $t('construction_site.name') }}</th>
        <th>{{ $t('construction_site.address') }}</th>
        <th>{{ $t('construction_site.created_at') }}</th>
        <th class="w-minimal">{{ $t('_action.toggle_association_construction_site.associated_short') }}</th>
      </tr>
      </thead>
      <tbody>
      <loading-indicator-table-body v-if="!constructionSites"/>
      <tr v-else-if="constructionSitesOrdered.length === 0">
        <td colspan="99">
          <p class="text-center">{{ $t('_view.no_construction_sites') }}</p>
        </td>
      </tr>
      <tr v-else v-for="constructionSite in constructionSitesOrdered" :key="constructionSite['@id']">
        <td>
          <image-lightbox :src="constructionSite.imageUrl" :subject="constructionSite.name"/>
        </td>
        <td>
          <a v-if="constructionSite.constructionManagers.includes(this.constructionManagerIri)" :href="getDashboardUrl(constructionSite)">
            {{ constructionSite.name }}
          </a>
          <span v-else>
             {{ constructionSite.name }}
          </span>
          <span class="ms-2 badge bg-secondary" v-if="constructionSite.isArchived">
            {{ $t('construction_site.archived') }}
          </span>
        </td>
        <td>{{ formatConstructionSiteAddress(constructionSite).join(', ') }}</td>
        <td>
          <date-time-human-readable :value="constructionSite.createdAt"/>
        </td>
        <td>
          <toggle-association-construction-site
              :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri"/>
        </td>
      </tr>
      </tbody>
      <caption class="caption-top" v-if="constructionSites.filter(c => c.isArchived).length">
        <div class="form-check-inline">
          <custom-checkbox for-id="show-archived" :label="$t('_view.construction_sites_participation_table.show_archived')">
            <input id="show-archived" class="form-check-input" type="checkbox" v-model="showArchived">
          </custom-checkbox>
        </div>
      </caption>
    </table>
  </div>
</template>

<script>

import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import {constructionSiteFormatter} from '../../services/formatters'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import ImageLightbox from './ImageLightbox'
import ToggleAssociationConstructionSite from '../Action/ToggleAssociationConstructionSite'
import {router} from "../../services/api";
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";

export default {
  emits: ['loaded-construction-sites'],
  components: {
    CustomCheckbox,
    ToggleAssociationConstructionSite,
    ImageLightbox,
    LoadingIndicatorTableBody,
    DateTimeHumanReadable,
  },
  data() {
    return {
      constructionSiteFilter: null,
      showArchived: false
    }
  },
  props: {
    isLoading: {
      type: Boolean,
      required: true
    },
    constructionSites: {
      type: Array,
      required: false
    },
    constructionManagerIri: {
      type: String,
      required: true
    }
  },
  methods: {
    formatConstructionSiteAddress: function (constructionSite) {
      return constructionSiteFormatter.address(constructionSite)
    },
    getDashboardUrl: function (constructionSite) {
      return router.constructionSiteDashboard(constructionSite)
    }
  },
  computed: {
    constructionSitesOrdered: function () {
      let candidates = [...this.constructionSites]
      if (!this.showArchived) {
        candidates = candidates.filter(a => !a.isArchived)
      }

      if (this.constructionSiteFilter) {
        const preparedSearch = this.constructionSiteFilter.toLocaleLowerCase()
        candidates = candidates.filter(a => a.name.toLocaleLowerCase().includes(preparedSearch))
      }

      return candidates.sort((a, b) => a.name.localeCompare(b.name))
    }
  }
}
</script>

<style scoped>
.mw-25 {
  max-width: 25em
}
</style>
