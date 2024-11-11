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
      <loading-indicator-table-body v-if="!constructionSites" />
      <tr v-else v-for="constructionSite in constructionSitesOrdered" :key="constructionSite['@id']">
        <td>
          <image-lightbox :src="constructionSite.imageUrl" :subject="constructionSite.name" />
        </td>
        <td>{{ constructionSite.name }}</td>
        <td>{{ formatConstructionSiteAddress(constructionSite).join(', ') }}</td>
        <td>
          <date-time-human-readable :value="constructionSite.createdAt" />
        </td>
        <td>
          <toggle-association-construction-site
              :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri" />
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import { constructionSiteFormatter } from '../../services/formatters'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import ImageLightbox from './ImageLightbox'
import ToggleAssociationConstructionSite from '../Action/ToggleAssociationConstructionSite'

export default {
  emits: ['loaded-construction-sites'],
  components: {
    ToggleAssociationConstructionSite,
    ImageLightbox,
    LoadingIndicatorTableBody,
    DateTimeHumanReadable,
  },
  data() {
    return {
      constructionSiteFilter: null
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
  },
  computed: {
    constructionSitesOrdered: function () {
      let candidates = [...this.constructionSites]
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
