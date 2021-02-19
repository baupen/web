const orderMixin = {
  emits: ["ordered"],
  props: {
    order: {
      type: Object,
      required: false
    },
    property: {
      type: String,
      required: false
    }
  },
  computed: {
    isActive: function () {
      return this.order && this.order.property === this.property
    },
    isAscOrdered: function () {
      return this.isActive && this.order.value === 'asc';
    },
    isDescOrdered: function () {
      return this.isActive && this.order.value === 'desc';
    }
  },
}

export { orderMixin }
