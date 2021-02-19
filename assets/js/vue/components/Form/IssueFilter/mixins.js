import { arraysAreEqual } from '../../../services/algorithms'

const entityFilterMixin = {
  emits: ['input'],
  data() {
    return {
      selectedEntities: []
    }
  },
  props: {
    entities: {
      type: Array,
      default: []
    },
    initialSelectedEntities: {
      type: Array,
      required: false
    },
  },
  watch: {
    selectedEntities: function () {
      this.$emit('input', [...this.selectedEntities])
    },
    entities: function () {
      this.selectedEntities = [...this.entities]
    }
  },
  methods: {
    toggleAllEntitiesSelected() {
      if (this.entityListsAreEqual(this.entities, this.selectedEntities)) {
        this.selectedEntities = []
      } else {
        this.selectedEntities = [...this.entities]
      }
    },
    entityListsAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    },
  },
  computed: {
    allEntitiesSelected: function () {
      return this.entityListsAreEqual(this.entities, this.selectedEntities);
    }
  },
  mounted() {
    if (this.initialSelectedEntities) {
      this.selectedEntities = [...this.initialSelectedEntities]
    } else {
      this.selectedEntities = [...this.entities]
    }
  }
}

export { entityFilterMixin }
