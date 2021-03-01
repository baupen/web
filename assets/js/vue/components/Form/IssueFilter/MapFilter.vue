<template>
  <div>
    <custom-checkbox-field
        id="filter-all-maps"
        @click.prevent="toggleAllEntitiesSelected"
        :label="$t('form.issue_filter.all_maps')">
      <input class="custom-control-input" type="checkbox"
             :indeterminate.prop="selectedEntities.length > 0 && !allEntitiesSelected"
             :checked="entities.length > 0 && allEntitiesSelected">
    </custom-checkbox-field>

    <hr />

    <div class="form-group">
      <custom-checkbox
          class="mb-1"
          v-for="mapContainer in mapContainers" :key="mapContainer.entity['@id']"
          :for-id="'filter-map-' + mapContainer.entity['@id']" :label="mapContainer.entity.name">
        <span :class="'spacer-' + mapContainer.level" />
        <input
            class="custom-control-input" type="checkbox" :id="'filter-map-' + mapContainer.entity['@id']"
            v-model="selectedEntities"
            :value="mapContainer.entity"
            @change="selected(mapContainer)"
        >
      </custom-checkbox>
    </div>
  </div>
</template>

<script>
import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
import CustomCheckbox from '../../Library/FormInput/CustomCheckbox'
import { mapTransformer } from '../../../services/transformers'
import { entityFilterMixin } from './mixins'

export default {
  components: {
    CustomCheckbox,
    CustomCheckboxField
  },
  mixins: [
    entityFilterMixin
  ],
  methods: {
    selected: function (mapContainer) {
      let entityIsNewlySelected = this.selectedEntities.includes(mapContainer.entity)
      let allChildrenOfOtherElectionState = this.collectChildrenIfExpectedSelectionState(mapContainer.children, !entityIsNewlySelected)

      if (allChildrenOfOtherElectionState) {
        if (entityIsNewlySelected) {
          // entity has been newly selected
          // if no children selected, then select all
          this.selectedEntities = [...this.selectedEntities, ...allChildrenOfOtherElectionState.map(c => c.entity)]
        } else {
          // entity has been newly deselected
          // if all children selected, then deselect all
          const deselectEntities = allChildrenOfOtherElectionState.map(c => c.entity)
          this.selectedEntities = this.selectedEntities.filter(s => !deselectEntities.includes(s))
        }
      }
    },
    collectChildrenIfExpectedSelectionState: function (children, expected) {
      let result = []
      for (let i = 0; i < children.length; i++) {
        const child = children[i]
        if (this.selectedEntities.includes(child.entity) !== expected) {
          return false
        }
        result.push(child)

        const recursiveResult = this.collectChildrenIfExpectedSelectionState(child.children, expected)
        if (!recursiveResult) {
          return false
        }
        result.push(...recursiveResult)
      }

      return result
    }
  },
  computed: {
    mapContainers: function () {
      return mapTransformer.orderedList(this.entities, mapTransformer.PROPERTY_LEVEL)
          .filter(c => !c.entity.isDeleted)
    }
  },
}
</script>

<style scoped="true" lang="scss">
@for $i from 1 through 10 {
  .spacer-#{$i} {
    padding-left: $i*1em;
  }
}

</style>
