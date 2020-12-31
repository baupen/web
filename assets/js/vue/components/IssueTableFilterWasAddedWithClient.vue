<template>
  <custom-checkbox-field for-id="filter-is-marked" :label="$t('issue.is_marked')">
    <input
        class="custom-control-input" type="checkbox" id="filter-is-marked"
        v-model="isMarked"
        :true-value="true"
        :false-value="false"
        :indeterminate.prop="isMarked === null"
    >
    <template v-slot:after>
      <div>
        <a class="btn-link clickable" v-if="isMarked !== null" @click="isMarked = null">
          {{ $t('edit_issues_button.actions.reset') }}
        </a>
      </div>
    </template>
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
      isMarked: false
    }
  },
  props: ['default'],
  watch: {
    isMarked: function () {
      this.$emit('input', this.isMarked)
    },
  },
  mounted() {
    this.isMarked = this.default
  }
}
</script>
