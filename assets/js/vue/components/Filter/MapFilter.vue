<template>
  <div>
    <custom-checkbox-field
        id="filter-all-maps"
        @click.prevent="toggleSelectedMaps(maps)"
        :label="$t('issue_table.filter_maps.all_maps')">
      <input class="custom-control-input" type="checkbox"
             :disabled="!maps"
             :checked="maps && maps.length > 0 && entityListsAreEqual(maps, selectedMaps)">
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
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import CustomCheckbox from '../Library/FormInput/CustomCheckbox'
import { arraysAreEqual } from '../../services/algorithms'
import { mapTransformer } from '../../services/transformers'

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
    },
  },
  watch: {
    selectedMaps: function () {
      this.$emit('input', this.selectedMaps)
    },
    maps: function () {
      this.selectedMaps = this.maps
    },
  },
  computed: {
    flattenedMaps: function () {
      return mapTransformer.flatHierarchy(this.maps)
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
    this.selectedMaps = this.maps
  }
}
</script>

<style scoped="true">
.spacer-1 {
  padding-left: 2em;
}

.spacer-2 {
  padding-left: 4em;
}

.spacer-3 {
  padding-left: 6em;
}

.spacer-4 {
  padding-left: 8em;
}

.spacer-5 {
  padding-left: 10em;
}

</style>
