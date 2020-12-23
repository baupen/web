<template>
  <div>
    <button @click="show = !show" class="btn btn-primary">{{ title }}</button>
    <transition name="fade">
      <modal v-if="show" @hide="show = false" :title="title" @keydown.esc="show = false" @keydown.enter="confirm">
        <template v-slot:modal-body>
          <slot></slot>
        </template>
        <template v-slot:modal-footer>
          <button type="submit" :disabled="!canConfirm" @click="confirm" class="btn btn-primary">
            {{ confirmTitle ?? title }}
          </button>
        </template>
      </modal>
    </transition>
  </div>
</template>

<script>

import Modal from "./Modal";

export default {
  emits: ['confirm'],
  components: {Modal},
  data() {
    return {
      show: false
    }
  },
  props: {
    title: {
      type: String,
      required: true
    },
    confirmTitle: {
      type: String,
      default: null
    },
    canConfirm: {
      type: Boolean,
      default: true
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
