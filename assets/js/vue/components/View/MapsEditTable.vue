<template>
  <table class="table table-hover border">
    <thead>
    <tr class="bg-light">
      <th>{{ $t('map.name') }}</th>
      <th>{{ $t('map.parent') }}</th>
      <th class="w-minimal" />
    </tr>
    </thead>
    <tbody>
    <table-body-loading-indicator v-if="!flatHierarchicalMaps" />
    <tr v-else v-for="flatHierarchy in flatHierarchicalMaps">
      <td>{{ flatHierarchy.entity.name }}</td>
      <td>
        {{ flatHierarchy.parent ? flatHierarchy.parent.name : '-' }}<br/>
      </td>
      <td>
        <div class="btn-group">
          <span /> <!-- fixes button css -->
          <!--
          <edit-map-button :map="map" />
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

export default {
  components: {
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
