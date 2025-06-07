<template>
  <div class="card">
    <div class="card-header clickable" @click="toggleActive">
      <span>
        <span class="card-header-icon">
            <font-awesome-icon :icon="['fal', 'chevron-right']" class="rotate-icon" :class="{'rotated': isActive}"/>
        </span>
        {{ title }}
      </span>
      <span class="text-end float-end">
        <button type="button" class="btn btn-toggle"
                :class="{'active': isActive}"
                @click.prevent.stop="toggleActive">
          <div class="handle"></div>
        </button>
      </span>
    </div>
    <div class="card-body bounded-height" v-if="isActive">
      <slot></slot>
    </div>
  </div>
</template>

<script>
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

export default {
  components: {FontAwesomeIcon},
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
      isActive: false
    }
  },
  watch: {
    isActive: function () {
      this.$emit('active-toggled', this.isActive)
    },
  },
  methods: {
    toggleActive: function () {
      this.isActive = !this.isActive
    }
  },
  mounted () {
    if (this.initialActivated) {
      this.toggleActive()
    }
  }
}
</script>

<style scoped>
.bounded-height {
  max-height: 30em;
  overflow: scroll;
}

.card-header-icon {
  margin-left: -0.25rem;
  margin-right: 0.25rem;
}

.rotate-icon {
  transition: transform 0.3s;
}

.rotated {
  transform: rotate(90deg);
}
</style>
