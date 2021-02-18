<template>
  <div>
    <custom-checkbox-field
        id="filter-all-craftsmen"
        @click.prevent="toggleSelectedCraftsmen(craftsmen)"
        :label="$t('form.issue_filter.all_craftsmen')">
      <input class="custom-control-input" type="checkbox"
             :indeterminate.prop="selectedCraftsmen.length > 0 && !allCraftsmenSelected"
             :checked="craftsmen.length > 0 && allCraftsmenSelected">
    </custom-checkbox-field>

    <hr/>

    <div class="form-group">
      <custom-checkbox
          class="mb-1"
          v-for="craftsman in craftsmen" :key="craftsman['@id']"
          :for-id="'filter-craftsman-' + craftsman['@id']" :label="craftsman.trade"
          :secondary-label="craftsman.company">
        <input
            class="custom-control-input" type="checkbox" :id="'filter-craftsman-' + craftsman['@id']"
            v-model="selectedCraftsmen"
            :value="craftsman"
        >
      </custom-checkbox>
    </div>
  </div>
</template>

<script>

import CustomCheckbox from '../../Library/FormInput/CustomCheckbox'
import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
import { arraysAreEqual } from '../../../services/algorithms'
export default {
  components: {
    CustomCheckboxField,
    CustomCheckbox
  },
  emits: ['input'],
  data() {
    return {
      selectedCraftsmen: []
    }
  },
  props: {
    craftsmen: {
      type: Array,
      default: []
    },
  },
  watch: {
    selectedCraftsmen: function () {
      this.$emit('input', [...this.selectedCraftsmen])
    },
    craftsmen: function () {
      this.selectedCraftsmen = [...this.craftsmen]
    }
  },
  methods: {
    toggleSelectedCraftsmen(toggleArray) {
      if (this.entityListsAreEqual(toggleArray, this.selectedCraftsmen)) {
        this.selectedCraftsmen = []
      } else {
        this.selectedCraftsmen = [...toggleArray]
      }
    },
    entityListsAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    },
  },
  computed: {
    allCraftsmenSelected: function () {
      return this.entityListsAreEqual(this.craftsmen, this.selectedCraftsmen);
    }
  },
  mounted() {
    this.selectedCraftsmen = [...this.craftsmen]
  }
}
</script>
