<template>
  <custom-checkbox-field for-id="filter-is-marked" :label="$t('issue.is_marked')">
    <input
        class="custom-control-input" type="checkbox" id="filter-is-marked"
        v-model="value"
        :true-value="true"
        :false-value="false"
        :indeterminate.prop="value === null"
    >
    <template v-slot:after>
      <div>
        <a class="btn-link clickable" v-if="value !== null" @click="value = null">
          {{ $t('edit_issues_button.actions.reset') }}
        </a>
      </div>
    </template>
  </custom-checkbox-field>
</template>


<script>

import CustomCheckboxField from '../../Library/FormLayout/CustomCheckboxField'

export default {
  components: {
    CustomCheckboxField

  },
  emits: ['input'],
  data () {
    return {
      value: false
    }
  },
  props: {
    default: {
      default: null
    }
  },
  watch: {
    value: function () {
      this.$emit('input', this.value)
    },
  },
  mounted () {
    this.value = this.default
  }
}
</script>
