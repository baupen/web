<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('map.name') }}</th>
      <th>{{ $t('map.parent') }}</th>
      <th class="w-minimal" />
      <th class="w-minimal" />
    </tr>
    </thead>
    <tbody>
    <table-body-loading-indicator v-if="!flatHierarchicalMaps" />
    <tr v-else v-for="flatHierarchy in flatHierarchicalMaps">
      <td>
        {{ "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(flatHierarchy.level) }}{{ flatHierarchy.entity.name }}
      </td>
      <td>
        {{ flatHierarchy.parent ? flatHierarchy.parent.name : $t('map.no_parent_name') }}<br/>
      </td>
      <td class="text-right">
        <file-render-lightbox class="h-btn" :src="flatHierarchy.entity.fileUrl" :subject="flatHierarchy.entity.name" />
      </td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <edit-map-button :map="flatHierarchy.entity" :maps="maps" />
          <!--
          <remove-map-button :construction-site="constructionSite" :map="map" />
          -->
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

export default {
  components: {
    EditMapButton,
    FileRenderLightbox,
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
        return null;
      }

      return  mapTransformer.flatHierarchy(this.maps)
    },
  },
}
</script>
