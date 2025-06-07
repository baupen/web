<template>
  <custom-checkbox :for-id="id" :label="label" :required="false">
    <input
        class="form-check-input" type="checkbox" :id="id"
        :checked="isOrdered"
        @input="toggleOrder"
    >
  </custom-checkbox>
</template>

<script>
import { orderMixin } from './mixins'
import CustomCheckbox from '../FormInput/CustomCheckbox'

export default {
  components: { CustomCheckbox },
  mixins: [orderMixin],
  props: {
    orderValue: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    id: {
      type: String,
      required: true
    }
  },
  computed: {
    isOrdered: function () {
      return this.orderValue === this.order
    },
  },
  methods: {
    toggleOrder: function () {
      if (!this.order) {
        this.$emit('order', this.orderValue)
      } else {
        this.$emit('order', null)
      }
    }
  }
}
</script>
