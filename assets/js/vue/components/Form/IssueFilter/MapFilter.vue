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

    <hr/>

    <div class="form-group">
      <custom-checkbox
          class="mb-1"
          v-for="map in flattenedMaps" :key="map.entity['@id']"
          :for-id="'filter-map-' + map.entity['@id']" :label="map.entity.name">
        <span :class="'spacer-' + map.level"/>
        <input
            class="custom-control-input" type="checkbox" :id="'filter-map-' + map.entity['@id']"
            v-model="selectedEntities"
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
import { entityFilterMixin } from './mixins'

export default {
  components: { CustomCheckbox, CustomCheckboxField },
  mixins: [
    entityFilterMixin
  ],
  computed: {
    flattenedMaps: function () {
      return mapTransformer.flatHierarchy(this.entities)
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
