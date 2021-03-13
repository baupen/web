<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('map.name') }}</th>
      <th>{{ $t('map.parent') }}</th>
      <th></th>
      <th class="w-thumbnail"></th>
      <th class="w-minimal"></th>
    </tr>
    </thead>
    <tbody>
    <table-body-loading-indicator v-if="!mapContainers" />
    <tr v-else-if="mapContainers.length === 0">
      <td colspan="99">
        <p class="text-center">{{ $t('_view.no_maps') }}</p>
      </td>
    </tr>
    <tr v-else v-for="mapContainer in mapContainers">
      <td>
        {{ '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(mapContainer.level) }}{{ mapContainer.entity.name }}
      </td>
      <td>
        {{ getParentName(mapContainer) }}<br />
      </td>
      <td>
        {{ getOriginalFilename(mapContainer.entity) }}
      </td>
      <td class="text-right">
        <map-render-lightbox class="h-btn" :construction-site="constructionSite" :map="mapContainer.entity" :empty="true" />
      </td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-map-button :map="mapContainer.entity" :maps="maps" />
          <remove-map-button v-if="!mapContainer.children.length" :construction-site="constructionSite" :map="mapContainer.entity" />
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>

import TableBodyLoadingIndicator from '../Library/View/LoadingIndicatorTableBody'
import { mapTransformer } from '../../services/transformers'
import MapRenderLightbox from './MapRenderLightbox'
import EditMapButton from '../Action/EditMapButton'
import RemoveMapButton from '../Action/RemoveMapButton'
import { mapFormatter } from '../../services/formatters'

export default {
  components: {
    RemoveMapButton,
    EditMapButton,
    MapRenderLightbox,
    TableBodyLoadingIndicator,
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      required: false
    }
  },
  computed: {
    mapContainers: function () {
      if (!this.maps) {
        return null
      }

      return mapTransformer.orderedList(this.maps, mapTransformer.PROPERTY_LEVEL | mapTransformer.PROPERTY_PARENT)
    },
  },
  methods: {
    getOriginalFilename: function (map) {
      return mapFormatter.originalFilename(map)
    },
    getParentName: function (mapContainer) {
      if (!mapContainer.entity.parent) {
        return this.$t('map.parent_not_set_name')
      }
      if (!mapContainer.parent) {
        return this.$t('map.parent_not_found_name')
      }

      return mapContainer.parent.entity.name
    }
  }
}
</script>
