<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('map.name') }}</th>
      <th>{{ $t('map.parent') }}</th>
      <th></th>
      <th class="w-minimal"></th>
      <th class="w-minimal"></th>
    </tr>
    </thead>
    <tbody>
    <table-body-loading-indicator v-if="!flatHierarchicalMaps" />
    <tr v-else v-for="flatHierarchy in flatHierarchicalMaps">
      <td>
        {{ '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(flatHierarchy.level) }}{{ flatHierarchy.entity.name }}
      </td>
      <td>
        {{ getParentName(flatHierarchy) }}<br />
      </td>
      <td>
        {{ getOriginalFilename(flatHierarchy.entity) }}
      </td>
      <td class="text-right">
        <map-render-lightbox class="h-btn" :map="flatHierarchy.entity" />
      </td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-map-button :map="flatHierarchy.entity" :maps="maps" />
          <remove-map-button v-if="!flatHierarchy.children.length" :construction-site="constructionSite" :map="flatHierarchy.entity" />
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
    flatHierarchicalMaps: function () {
      if (!this.maps) {
        return null
      }

      return mapTransformer.flatHierarchy(this.maps)
    },
  },
  methods: {
    getOriginalFilename: function (map) {
      return mapFormatter.originalFilename(map)
    },
    getParentName: function (flatHierarchy) {
      if (!flatHierarchy.entity.parent) {
        return this.$t('view.maps_edit.no_parent_name')
      }
      if (!flatHierarchy.parent) {
        return this.$t('view.maps_edit.parent_not_found')
      }

      return flatHierarchy.parent.name
    }
  }
}
</script>
