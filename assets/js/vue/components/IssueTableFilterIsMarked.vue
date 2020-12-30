<template>
  <custom-checkbox-field for-id="filter-was-added-with-client" :label="$t('issue.was_added_with_client')">
    <input
        class="custom-control-input" type="checkbox" id="filter-was-added-with-client"
        v-model="wasAddedWithClient"
        :true-value="true"
        :false-value="false"
        :indeterminate.prop="wasAddedWithClient === null"
    >
    <template v-slot:after>
      <div>
        <a class="btn-link clickable" v-if="wasAddedWithClient !== null"
           @click="wasAddedWithClient = null">
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
      wasAddedWithClient: false
    }
  },
  props: ['default'],
  watch: {
    wasAddedWithClient: function () {
      this.$emit('input', this.wasAddedWithClient)
    },
  },
  mounted() {
    this.wasAddedWithClient = this.default
  }
}
</script>
