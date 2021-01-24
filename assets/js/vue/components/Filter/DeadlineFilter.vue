<template>
  <div>
    <div class="form-row">
      <form-field class="col-md-6" for-id="deadlineBefore"
                  :label="$t('issue.deadline') + ' ' + $t('issue_table.filter_time.before')">
        <flat-pickr
            id="deadlineBefore" class="form-control"
            v-model="deadlineBefore"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
      <form-field class="col-md-6" for-id="deadlineAfter"
                  :label="$t('issue.deadline') + ' ' + $t('issue_table.filter_time.after')">
        <flat-pickr
            id="deadlineAfter" class="form-control"
            v-model="deadlineAfter"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
    </div>
  </div>
</template>


<script>

import {dateConfig, flatPickr} from "../../services/flatpickr";
import FormField from '../Library/FormLayout/FormField'

export default {
  components: {
    FormField,
    flatPickr},
  emits: [
    'input-deadline-before',
    'input-deadline-after',
  ],
  data() {
    return {
      deadlineBefore: null,
      deadlineAfter: null
    }
  },
  watch: {
    deadlineBefore: function () {
      this.$emit('input-deadline-before', this.normalize(this.deadlineBefore))
    },
    deadlineAfter: function () {
      this.$emit('input-deadline-after', this.normalize(this.deadlineAfter))
    }
  },
  methods: {
    normalize: function (value) {
      if (!value) {
        return null
      }

      return value;
    }
  },
  computed: {
    dateTimePickerConfig: function () {
      return dateConfig;
    }
  }
}
</script>
