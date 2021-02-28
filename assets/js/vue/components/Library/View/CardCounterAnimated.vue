<template>
  <div class="card text-center">
    <div class="card-body">
      <h3>
        <span class="border-bottom pb-1" :class="'border-' + color">
          {{ currentNumber }}
        </span>
      </h3>
      <p class="card-text">{{ description }}</p>
    </div>
  </div>
</template>

<script>

const frameDuration = 20
const maxFrames = 1000 / frameDuration
const easingFunction = t => t * t * (3.0 - 2.0 * t) // bezier

export default {
  data () {
    return {
      multiplier: 1,
      currentMultiplierAnimator: null,
    }
  },
  props: {
    target: {
      type: Number,
      required: true
    },
    description: {
      type: String,
      required: true
    },
    color: {
      type: String,
      required: true
    }
  },
  watch: {
    target: function () {
      this.startMultiplierAnimator()
    }
  },
  methods: {
    tryStopCurrentMultiplierAnimator: function () {
      if (!this.currentMultiplierAnimator) {
        return
      }

      clearInterval(this.currentMultiplierAnimator)
      this.currentMultiplierAnimator = null
    },
    startMultiplierAnimator: function () {
      this.tryStopCurrentMultiplierAnimator()
      this.multiplier = 0

      let currentFrame = 0
      this.currentMultiplierAnimator = setInterval(() => {
        currentFrame++

        this.multiplier = easingFunction(currentFrame / maxFrames)

        if (currentFrame >= maxFrames) {
          this.multiplier = 1
          this.tryStopCurrentMultiplierAnimator()
        }
      }, frameDuration)
    }
  },
  computed: {
    currentNumber: function () {
      return Math.round(this.target * this.multiplier)
    }
  }
}
</script>

<style scoped="true">
.underline {
  text-decoration: underline;
}
</style>
