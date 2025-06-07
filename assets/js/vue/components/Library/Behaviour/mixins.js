const orderMixin = {
  emits: ['order'],
  props: {
    order: {
      type: String,
      required: false
    },
  },
  computed: {
    isAscOrdered: function () {
      return this.order === 'asc'
    },
    isDescOrdered: function () {
      return this.order === 'desc'
    }
  }
}

export { orderMixin }
