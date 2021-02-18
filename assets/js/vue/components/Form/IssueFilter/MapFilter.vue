<template>
  <div>
    <custom-checkbox-field
        id="filter-all-maps"
        @click.prevent="toggleSelectedMaps(maps)"
        :label="$t('form.issue_filter.all_maps')">
      <input class="custom-control-input" type="checkbox"
             :indeterminate.prop="selectedMaps.length > 0 && !allMapsSelected"
             :checked="maps.length > 0 && allMapsSelected">
    </custom-checkbox-field>

    <hr/>

    <div class="form-group">
      <custom-checkbox
          class="mb-1"
          v-for="map in flattenedMaps" :key="map.entity['@id']"
          :for-id="'filter-map-' + map.entity['@id']" :label="map.entity.name">
        <span :class="'spacer-' + map.level"/>
        <input
            class="custom-control-input" type="checkbox" :id="'filter-map-' + map.entity['@id']"
            v-model="selectedMaps"
            :value="map.entity"
        >
      </custom-checkbox>
    </div>
  </div>
</template>

<script>
import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
import CustomCheckbox from '../../Library/FormInput/CustomCheckbox'
import { arraysAreEqual } from '../../../services/algorithms'
import { mapTransformer } from '../../../services/transformers'

export default {
  components: { CustomCheckbox, CustomCheckboxField },
  emits: ['input'],
  data() {
    return {
      selectedMaps: []
    }
  },
  props: {
    maps: {
      type: Array,
      default: []
    },
  },
  watch: {
    selectedMaps: function () {
      this.$emit('input', this.selectedMaps)
    },
    maps: function () {
      this.selectedMaps = [...this.maps]
    },
  },
  computed: {
    flattenedMaps: function () {
      return mapTransformer.flatHierarchy(this.maps)
    },
    allMapsSelected: function () {
      return this.entityListsAreEqual(this.maps, this.selectedMaps)
    }
  },
  methods: {
    toggleSelectedMaps(toggleArray) {
      if (this.entityListsAreEqual(toggleArray, this.selectedMaps)) {
        this.selectedMaps = []
      } else {
        this.selectedMaps = [...toggleArray]
      }
    },
    entityListsAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    },
  },
  mounted() {
    this.selectedMaps = [...this.maps]
  }
}
</script>

<style scoped="true" lang="scss">
@for $i from 1 through 10 {
  .spacer-#{$i} {
    padding-left: $i*1em;
  }
}

</style>
