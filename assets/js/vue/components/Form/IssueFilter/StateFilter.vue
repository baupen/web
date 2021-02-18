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

    <custom-checkbox-field
        for-id="filter-state-is-closed" :label="$t('issue.state.closed')">
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

import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'
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
  watch: {
    state: function () {
      this.updateState()
    },
  },
  computed: {
    state: function () {
      let state = 0;
      if (this.isRegistered) {
        state = state | 2;
      }
      if (this.isResolved) {
        state = state | 4;
      }
      if (this.isClosed) {
        state = state | 8;
      }

      return state > 0 ? state : null;
    }
  },
  methods: {
    updateState: function () {
      this.$emit('input', this.state)
    }
  }
}
</script>
