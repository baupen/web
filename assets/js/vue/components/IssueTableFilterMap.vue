<template>

  <custom-checkbox-field
      id="filter-all-maps"
      @click.prevent="toggleSelectedMaps(maps)"
      :label="$t('issue_table.filter_maps.all_maps')">
    <input class="custom-control-input" type="checkbox"
           :disabled="maps === null"
           :checked="maps !== null && maps.length > 0 && entityListsAreEqual(maps, selectedMaps)">
  </custom-checkbox-field>

  <hr/>

  <custom-checkbox-field
      v-for="map in flattenedMaps" :key="map.entity['@id']"
      :for-id="'filter-map-' + map.entity['@id']" :label="map.entity.name">
    <span :class="'spacer-' + map.level"/>
    <input
        class="custom-control-input" type="checkbox" :id="'filter-map-' + map.entity['@id']"
        v-model="selectedMaps"
        :value="map.entity"
    >
  </custom-checkbox-field>
</template>

<script>
import CustomCheckboxField from "./Edit/Layout/CustomCheckboxField";
import {arraysAreEqual} from "../services/algorithms";

const rootKey = 'root'

export default {
  components: {CustomCheckboxField},
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
    hierarchicalMaps: function () {
      let parentLookup = {}
      this.maps.forEach(m => {
        const parentKey = m.parent ?? rootKey

        if (!parentLookup[parentKey]) {
          parentLookup[parentKey] = []
        }

        parentLookup[parentKey].push(m)
      })

      return this.getChildren(rootKey, parentLookup)
    },
    flattenedMaps: function () {
      let hierarchicalMaps = this.hierarchicalMaps;
      this.sortChildren(hierarchicalMaps)

      return this.flattenChildren(hierarchicalMaps)
    }
  },
  methods: {
    getChildren: function (key, parentLookup) {
      if (!(key in parentLookup)) {
        return []
      }

      return parentLookup[key].map(entry => ({
        entity: entry,
        children: this.getChildren(entry['@id'], parentLookup)
      }))
    },
    sortChildren: function (children) {
      children.sort((a, b) => a.entity.name.localeCompare(b.entity.name))
      children.forEach(child => {
        this.sortChildren(child.children)
      })
    },
    flattenChildren: function (children, level = 0) {
      let result = []
      children.forEach(child => {
        result.push({entity: child.entity, level})
        result = result.concat(...this.flattenChildren(child.children, level + 1))
      })

      return result
    },
    toggleSelectedMaps(toggleArray) {
      if (this.entityListsAreEqual(toggleArray, this.selectedMaps)) {
        this.selectedMaps = []
      } else {
        this.selectedMaps = [...toggleArray]
      }
    },
    toggleSelectedMap(toggleArray) {
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
