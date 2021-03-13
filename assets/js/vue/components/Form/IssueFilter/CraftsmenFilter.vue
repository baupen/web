<template>
  <div>
    <custom-checkbox-field
        id="filter-all-craftsmen"
        @click.prevent="toggleAllEntitiesSelected"
        :label="$t('_form.issue_filter.all_craftsmen')">
      <input class="custom-control-input" type="checkbox"
             :indeterminate.prop="selectedEntities.length > 0 && !allEntitiesSelected"
             :checked="entities.length > 0 && allEntitiesSelected">
    </custom-checkbox-field>

    <hr/>

    <div class="form-group">
      <custom-checkbox
          class="mb-1"
          v-for="craftsman in orderedCraftsmen" :key="craftsman['@id']"
          :for-id="'filter-craftsman-' + craftsman['@id']" :label="craftsman.trade"
          :secondary-label="craftsman.company">
        <input
            class="custom-control-input" type="checkbox" :id="'filter-craftsman-' + craftsman['@id']"
            v-model="selectedEntities"
            :value="craftsman"
        >
      </custom-checkbox>
    </div>
  </div>
</template>

<script>

import CustomCheckbox from '../../Library/FormInput/CustomCheckbox'
import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
import { entityFilterMixin } from './mixins'
export default {
  components: {
    CustomCheckboxField,
    CustomCheckbox
  },
  mixins: [
    entityFilterMixin
  ],
  computed: {
    orderedCraftsmen: function () {
      return this.entities.sort((a, b) => a.trade.localeCompare(b.trade))
          .filter(c => !c.isDeleted)
    }
  }
}
</script>
