<template>
  <div>
    <custom-checkbox-field for-id="filter-state-is-open" :label="$t('issue.state.open')">
      <input
          class="custom-control-input" type="checkbox" id="filter-state-is-open"
          v-model="isRegistered"
          :true-value="true"
          :false-value="false"
      >
    </custom-checkbox-field>

    <custom-checkbox-field for-id="filter-state-is-resolved" :label="$t('issue.state.resolved')">
      <input
          class="custom-control-input" type="checkbox" id="filter-state-is-resolved"
          v-model="isResolved"
          :true-value="true"
          :false-value="false"
      >
    </custom-checkbox-field>

    <custom-checkbox-field for-id="filter-state-is-closed" :label="$t('issue.state.closed')">
      <input
          class="custom-control-input" type="checkbox" id="filter-state-is-closed"
          v-model="isClosed"
          :true-value="true"
          :false-value="false"
      >
    </custom-checkbox-field>
  </div>
</template>


<script>


import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
export default {
  components: { CustomCheckboxField },
  emits: ['input'],
  data() {
    return {
      isRegistered: true,
      isResolved: true,
      isClosed: true,
    }
  },
  props: {
    defaultIsRegistered: {
      default: true
    },
    defaultIsClosed: {
      default: true
    },
    defaultIsResolved: {
      default: true
    },
  },
  watch: {
    isRegistered: function () {
      this.updateState()
    },
    isResolved: function () {
      this.updateState()
    },
    isClosed: function () {
      this.updateState()
    },
  },
  methods: {
    updateState: function () {
      let state = 0;
      if (this.isRegistered || this.minimalState >= 1) {
        state = state | 1;
      }
      if (this.isResolved || this.minimalState >= 2) {
        state = state | 2;
      }
      if (this.isClosed || this.minimalState >= 4) {
        state = state | 4;
      }

      this.$emit('input', state)
    }
  },
  mounted() {
    this.isRegistered = this.defaultIsRegistered
    this.isResolved = this.defaultIsResolved
    this.isClosed = this.defaultIsClosed

    this.updateState()
  }
}
</script>
