<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th></th>
      <th>{{ $t('construction_site.name') }}</th>
      <th>{{ $t('construction_site.address') }}</th>
      <th class="w-minimal"></th>
    </tr>
    </thead>
    <tbody>
    <table-body-loading-indicator v-if="!orderedConstructionSites" />
    <tr v-else v-for="constructionSite in orderedConstructionSites">
      <td>
        <image-lightbox :src="constructionSite.imageUrl" :subject="constructionSite.name" />
      </td>
      <td>
        {{ getAddress(constructionSite).join(", ") }}
      </td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-construction-site :construction-site="constructionSite" />
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>

import TableBodyLoadingIndicator from '../Library/View/LoadingIndicatorTableBody'
import { mapTransformer } from '../../services/transformers'
import FileRenderLightbox from './FileRenderLightbox'
import EditMapButton from '../Action/EditMapButton'
import RemoveMapButton from '../Action/RemoveMapButton'
import { constructionSiteFormatter, mapFormatter } from '../../services/formatters'
import ImageLightbox from './ImageLightbox'

export default {
  components: {
    ImageLightbox,
    RemoveMapButton,
    EditMapButton,
    FileRenderLightbox,
    TableBodyLoadingIndicator,
  },
  props: {
    constructionSites: {
      type: Array,
      required: false
    }
  },
  computed: {
    orderedConstructionSites: function () {
      if (!this.constructionSites) {
        return null
      }

      return this.constructionSites.sort((a, b) => a.name.localeCompare(b.name))
    }
  },
  methods: {
    getAddress: function (constructionSite) {
      return constructionSiteFormatter.address(constructionSite)
    }
  }
}
</script>
