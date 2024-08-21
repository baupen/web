<template>
  <div class="map-container">
    <img ref="map" :src="src" class="img-fluid img-within-modal img-set-position border" :alt="'image of ' + map.name"
         @click="selectPosition"/>
    <canvas ref="map-canvas" class="map-canvas" />
  </div>
</template>
<script>
import {api} from "../../services/api";

export default {
  components: {
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
    drawCanvas: function () {
      const canvas = this.$refs['map-canvas']
      const rect = canvas.getBoundingClientRect();
      const ctx = canvas.getContext('2d');


      // Clear the canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      if (!this.position) {
        return
      }

      // ensure high resolution
      const dpr = window.devicePixelRatio || 1;
      canvas.width = rect.width * dpr;
      canvas.height = rect.height * dpr;

      // Calculate pixel position from percentage
      const imageRect = this.$refs['map'].getBoundingClientRect();
      const leftShift = imageRect.left - rect.left;
      const marginHorizontal = leftShift + rect.right - imageRect.right
      const xPos = (canvas.width - marginHorizontal) * this.position.x + leftShift;
      const topShift = imageRect.top - rect.top
      const marginVertical = topShift + rect.bottom - imageRect.bottom
      const yPos = (canvas.height - marginVertical) * this.position.y + topShift;

      const crosshairSize = 20; // Length of the crosshair lines
      const linewidth = 2;

      // draw the circle around the crosshair
      ctx.beginPath();
      ctx.arc(xPos, yPos, crosshairSize, 0, 2 * Math.PI);
      ctx.strokeStyle = 'white';
      ctx.lineWidth = linewidth * 3;
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(xPos, yPos, crosshairSize, 0, 2 * Math.PI);
      ctx.strokeStyle = 'black';
      ctx.lineWidth = linewidth * 2;
      ctx.stroke();

      // draw the crosshair
      ctx.strokeStyle = 'black';
      ctx.lineWidth = linewidth;
      ctx.beginPath();
      ctx.moveTo(xPos - crosshairSize, yPos);
      ctx.lineTo(xPos + crosshairSize, yPos);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(xPos, yPos - crosshairSize);
      ctx.lineTo(xPos, yPos + crosshairSize);
      ctx.stroke();
    },
    selectPosition: function (event) {
      const rect = this.$refs['map'].getBoundingClientRect();

      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;

      const width = rect.width;
      const height = rect.height;

      const xPercentage = x / width;
      const yPercentage = y / height;

      this.position = {x: xPercentage, y: yPercentage, zoomScale: 1}
      this.drawCanvas()
    },
  },
  computed: {
    src: function () {
      if (!this.map.fileUrl) {
        return null
      }

      return api.getIssuesRenderLink(this.constructionSite, this.map, {number: -1})
    },
  },
  mounted () {
    this.$emit('update', this.position)
  }
}
</script>

<style scoped>
.map-container {
  position: relative;
}

.img-within-modal {
  max-height: 76vh;
  display: block;
  margin: 0 auto;
}

.img-set-position {
  cursor: crosshair;
}

.map-canvas {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  pointer-events: none;
}

</style>
