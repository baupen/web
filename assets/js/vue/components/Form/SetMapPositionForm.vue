<template>
  <map-position-canvas
      :construction-site="constructionSite" :map="map" :position="position ?? currentPosition"
      :can-position="true" @positioned="positioned"/>
</template>
<script>
import MapPositionCanvas from "../View/MapPositionCanvas.vue";

export default {
  components: {
    MapPositionCanvas
  },
  emits: ['update'],
  data () {
    return {
      position: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    currentPosition: {
      type: Object,
      required: false,
    },
  },
  watch: {
    position: {
      deep: true,
      handler: function () {
        this.$emit('update', this.position)
      }
    },
  },
  methods: {
    positioned: function (position) {
      this.position = {...position, zoomScale: 1}
    },
  },
  mounted () {
    this.$emit('update', this.position)
  }
}
</script>
