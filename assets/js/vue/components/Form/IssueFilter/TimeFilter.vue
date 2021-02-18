<template>
  <div>
    <div class="form-row">
      <form-field class="col-md-6" for-id="registeredAtBefore"
                  :label="$t('issue.state.registered') + ' ' + $t('issue_table.filter_time.before')">
        <flat-pickr
            id="registeredAtBefore" class="form-control"
            v-model="registeredAtBefore"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
      <form-field class="col-md-6" for-id="registeredAtAfter"
                  :label="$t('issue.state.registered') + ' ' + $t('issue_table.filter_time.after')">
        <flat-pickr
            id="registeredAtAfter" class="form-control"
            v-model="registeredAtAfter"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
    </div>

    <div class="form-row">
      <form-field class="col-md-6" for-id="resolvedAtBefore"
                  :label="$t('issue.state.resolved') + ' ' + $t('issue_table.filter_time.before')">
        <flat-pickr
            id="resolvedAtBefore" class="form-control"
            v-model="resolvedAtBefore"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
      <form-field class="col-md-6" for-id="resolvedAtAfter"
                  :label="$t('issue.state.resolved') + ' ' + $t('issue_table.filter_time.after')">
        <flat-pickr
            id="resolvedAtAfter" class="form-control"
            v-model="resolvedAtAfter"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
    </div>

    <div class="form-row">
      <form-field class="col-md-6" for-id="closedAtBefore"
                  :label="$t('issue.state.closed') + ' ' + $t('issue_table.filter_time.before')">
        <flat-pickr
            id="closedAtBefore" class="form-control"
            v-model="closedAtBefore"
            :config="dateTimePickerConfig">
        </flat-pickr>
      </form-field>
      <form-field class="col-md-6" for-id="closedAtAfter"
                  :label="$t('issue.state.closed') + ' ' + $t('issue_table.filter_time.after')">
        <flat-pickr
            id="closedAtAfter" class="form-control"
            v-model="closedAtAfter"
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
    'input-registered-at-before',
    'input-registered-at-after',
    'input-resolved-at-before',
    'input-resolved-at-after',
    'input-closed-at-before',
    'input-closed-at-after'
  ],
  data() {
    return {
      registeredAtBefore: null,
      registeredAtAfter: null,
      resolvedAtBefore: null,
      resolvedAtAfter: null,
      closedAtBefore: null,
      closedAtAfter: null
    }
  },
  watch: {
    registeredAtBefore: function () {
      this.$emit('input-registered-at-before', this.normalize(this.registeredAtBefore))
    },
    registeredAtAfter: function () {
      this.$emit('input-registered-at-after', this.normalize(this.registeredAtAfter))
    },
    resolvedAtBefore: function () {
      this.$emit('input-resolved-at-before', this.normalize(this.resolvedAtBefore))
    },
    resolvedAtAfter: function () {
      this.$emit('input-resolved-at-after', this.normalize(this.resolvedAtAfter))
    },
    closedAtBefore: function () {
      this.$emit('input-closed-at-before', this.normalize(this.closedAtBefore))
    },
    closedAtAfter: function () {
      this.$emit('input-closed-at-after', this.normalize(this.closedAtAfter))
    },
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
