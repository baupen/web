<template>
  <div>
    <table class="table table-hover table-striped">
      <thead>
      <tr>
        <th class="w-minimal"></th>
        <th>{{ $t('construction_site.name') }}</th>
        <th>{{ $t('construction_site.address') }}</th>
        <th>{{ $t('construction_site.created_at') }}</th>
        <th class="w-minimal"></th>
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
          <button type="button" class="btn btn-toggle"
                  :class="{'active': ownsConstructionSite(constructionSite)}"
                  @click="toggleOwnConstructionSite(constructionSite)">
            <div class="handle"></div>
          </button>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>

import ConstructionSiteCard from './ConstructionSitesEnterMasonryCard'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import { constructionSiteFormatter } from '../../services/formatters'
import { api } from '../../services/api'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import ImageLightbox from './ImageLightbox'

export default {
  emits: ['loaded-construction-sites'],
  components: {
    ImageLightbox,
    LoadingIndicatorTableBody,
    DateTimeHumanReadable,
    ConstructionSiteCard
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
    ownsConstructionSite: function (constructionSite) {
      return constructionSite.constructionManagers.includes(this.constructionManagerIri)
    },
    toggleOwnConstructionSite: function (constructionSite) {
      const ownsConstructionSite = this.ownsConstructionSite(constructionSite)
      const constructionManagers = constructionSite.constructionManagers.filter(cm => cm !== this.constructionManagerIri)
      if (ownsConstructionSite) {
        api.patch(constructionSite, { constructionManagers }, this.$t('switch.messages.success.removed_self'))
      } else {
        constructionManagers.push(this.constructionManagerIri)
        api.patch(constructionSite, { constructionManagers }, this.$t('switch.messages.success.added_self'))
      }
    }
  },
  computed: {
    constructionSitesOrdered: function () {
      return this.constructionSites.sort((a, b) => a.name.localeCompare(b.name))
    }
  }
}
</script>
