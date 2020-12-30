<template>

  <custom-checkbox-field
      id="filter-all-craftsmen"
      @click.prevent="toggleSelectedCraftsmen(craftsmen)"
      :label="$t('issue_table.filter_craftsman.all_craftsmen')">
    <input class="custom-control-input" type="checkbox"
           :disabled="craftsmen === null"
           :checked="craftsmen !== null && craftsmen.length > 0 && entityListsAreEqual(craftsmen, selectedCraftsmen)">
  </custom-checkbox-field>

  <hr/>

  <custom-checkbox-field
      v-for="craftsman in craftsmen" :key="craftsman['@id']"
      :for-id="'filter-craftsman-' + craftsman['@id']" :label="craftsman.company" :secondary-label="craftsman.trade">
    <input
        class="custom-control-input" type="checkbox" :id="'filter-craftsman-' + craftsman['@id']"
        v-model="selectedCraftsmen"
        :value="craftsman"
    >
  </custom-checkbox-field>
</template>


<script>
import CustomCheckboxField from "./Edit/Layout/CustomCheckboxField";
import {arraysAreEqual} from "../services/algorithms";

export default {
  components: {CustomCheckboxField},
  emits: ['input'],
  data() {
    return {
      selectedCraftsmen: []
    }
  },
  props: {
    craftsmen: {
      type: Array,
    },
  },
  watch: {
    selectedCraftsmen: {
      deep: true,
      handler: function () {
        console.log('selected craftsmen: ', this.selectedCraftsmen)

        const publishedList = this.selectedCraftsmen.length === this.craftsmen.length ? [] : this.selectedCraftsmen
        console.log('published list craftsmen: ', publishedList)

        this.$emit('input', publishedList)
      }
    },
    craftsmen: function () {
      this.selectedCraftsmen = this.craftsmen
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
  }
}
</script>
