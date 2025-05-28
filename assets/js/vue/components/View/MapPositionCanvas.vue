<template>
  <div class="map-container">
    <img :src="src" ref="map" class="img-fluid img-sized border" :class="{'img-sized-inline': inline, 'img-positioning': canPosition}" :alt="'image of ' + map.name"
         @load="drawCanvas" @click="capturePosition" />
    <canvas ref="map-canvas" class="map-canvas" />
  </div>
</template>
<script>
import {api} from "../../services/api";

const getCanvasPosition = function (position, canvasRect, imageRect) {
  const leftShift = imageRect.left - canvasRect.left;
  const marginHorizontal = leftShift + canvasRect.right - imageRect.right
  const xPos = (canvasRect.width - marginHorizontal) * position.x + leftShift;
  const topShift = imageRect.top - canvasRect.top
  const marginVertical = topShift + canvasRect.bottom - imageRect.bottom
  const yPos = (canvasRect.height - marginVertical) * position.y + topShift;

  return {x: xPos, y: yPos}
}

export default {
  emits: ['positioned'],
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    position: {
      type: Object,
      required: true
    },
    inline: {
      type: Boolean,
      default: false
    },
    canPosition: {
      type: Boolean,
      default: false,
    }
  },
  watch: {
    position: {
      deep: true,
      handler: function () {
        this.drawCanvas()
      }
    },
  },
  methods: {
    drawCanvas: function () {
      const canvas = this.$refs['map-canvas']
      const rect = canvas.getBoundingClientRect();
      const ctx = canvas.getContext('2d');

      // ensure high resolution & reset
      const dpr = window.devicePixelRatio || 1;
      canvas.width = rect.width * dpr;
      canvas.height = rect.height * dpr;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.scale(dpr, dpr);

      if (!this.position) {
        return
      }

      // Calculate pixel position from percentage
      const imageRect = this.$refs['map'].getBoundingClientRect();
      const position = getCanvasPosition(this.position, rect, imageRect)

      const crosshairSize = 20; // Length of the crosshair lines
      const linewidth = 2;

      // draw the circle around the crosshair
      ctx.beginPath();
      ctx.arc(position.x, position.y, crosshairSize, 0, 2 * Math.PI);
      ctx.strokeStyle = 'white';
      ctx.lineWidth = linewidth * 3;
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(position.x, position.y, crosshairSize, 0, 2 * Math.PI);
      ctx.strokeStyle = 'black';
      ctx.lineWidth = linewidth * 2;
      ctx.stroke();

      // draw the crosshair
      ctx.strokeStyle = 'black';
      ctx.lineWidth = linewidth;
      ctx.beginPath();
      ctx.moveTo(position.x - crosshairSize, position.y);
      ctx.lineTo(position.x + crosshairSize, position.y);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(position.x, position.y - crosshairSize);
      ctx.lineTo(position.x, position.y + crosshairSize);
      ctx.stroke();
    },
    capturePosition: function (event) {
      const rect = this.$refs['map'].getBoundingClientRect();

      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;

      const width = rect.width;
      const height = rect.height;

      const xPercentage = x / width;
      const yPercentage = y / height;

      this.$emit('positioned', {x: xPercentage, y: yPercentage})
    }
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
    this.drawCanvas()
  }
}
</script>

<style scoped>
.map-container {
  position: relative;
}

.img-sized {
  max-height: 76vh;
  display: block;
}

.img-sized-inline {
  max-height: 10em;
}

.img-positioning {
  margin: 0 auto;
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
