<template>
  <div class="card">
    <div class="card-header clickable" @click="toggleOpen">
      <span>
        {{ title }}
      </span>
      <span class="text-right float-right">
        <button type="button" class="btn btn-toggle"
                :class="{'active': isActive}"
                @click.prevent.stop="toggleActive">
          <div class="handle"></div>
        </button>
      </span>
    </div>
    <div class="card-body" v-if="isOpen">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  emits: ['active-toggled'],
  props: {
    title: {
      type: String,
      required: true
    },
    initialActivated: {
      type: Boolean,
      required: true
    }
  },
  data () {
    return {
      isOpen: false,
      isActive: false
    }
  },
  watch: {
    isActive: function () {
      this.$emit('active-toggled', this.isActive)
    },
  },
  methods: {
    toggleOpen: function () {
      if (this.isOpen) {
        this.isOpen = false
      } else {
        this.isOpen = this.isActive = true
      }
    },
    toggleActive: function () {
      if (this.isActive) {
        this.isActive = this.isOpen = false
      } else {
        this.isActive = this.isOpen = true
      }
    }
  },
  mounted () {
    if (this.initialActivated) {
      this.toggleActive()
    }
  }
}
</script>
