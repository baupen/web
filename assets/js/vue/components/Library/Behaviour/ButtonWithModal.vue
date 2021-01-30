<template>
  <button @click="show = !show" :disabled="buttonDisabled" :class="'btn btn-outline-' + color">
    <slot name="button-content">
      {{ title }}
    </slot>
  </button>
  <transition name="fade">
    <modal v-if="show" :size="modalSize" @hide="show = false" :title="title" @keydown.esc="show = false">
      <template v-slot:body>
        <slot></slot>
      </template>
    </modal>
  </transition>
</template>

<script>

import Modal from "./Modal";

export default {
  emits: ['confirm', 'shown', 'hidden'],
  components: {Modal},
  data() {
    return {
      show: false
    }
  },
  props: {
    title: {
      type: String,
      required: false
    },
    buttonDisabled: {
      type: Boolean,
      default: false
    },
    modalSize: {
      type: String,
      default: null
    },
    color: {
      type: String,
      default: 'primary'
    }
  },
  watch: {
    show: function () {
      this.$nextTick(() => {
        if (this.show) {
          this.$emit('shown')
        } else {
          this.$emit('hidden')
        }
      })
    }
  },
  methods: {
    confirm: function () {
      this.$emit('confirm');
      this.show = false;
    }
  }
}
</script>


<style scoped="true">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

</style>
