<template>
  <custom-checkbox :for-id="id" :label="label" :required="false">
    <input
        class="custom-control-input" type="checkbox" :id="id"
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
      return this.orderValue === 'desc' ? this.isDescOrdered : this.isAscOrdered
    },
  },
  methods: {
    toggleOrder: function () {
      // toggle states: !isActive => isAscOrdered => !isActive
      if (!this.isActive) {
        this.$emit('ordered', {property: this.property, value: this.orderValue})
      } else {
        this.$emit('ordered', null)
      }
    }
  }
}
</script>
