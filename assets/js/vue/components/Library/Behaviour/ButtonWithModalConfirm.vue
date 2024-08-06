<template>
  <button @click="show = !show" :disabled="buttonDisabled" :class="'btn btn-outline-' + color">
    <slot name="button-content">
      {{ title }}
    </slot>
  </button>
  <transition name="fade">
    <modal v-if="show" :size="modalSize" @hide="show = false" :title="title" @keydown.esc="show = false">
      <template v-slot:header>
        <slot name="header">
          <h5 class="modal-title">{{ title }}</h5>
        </slot>
      </template>
      <template v-slot:body>
        <slot></slot>
      </template>
      <template v-slot:after-body>
        <slot name="footer">
          <div class="modal-footer">
            <button v-if="canAbort" type="submit" @click="abort" class="btn btn-light me-auto">
              {{ abortTitle }}
            </button>
            <slot name="secondary-footer"></slot>
            <button type="submit" :disabled="!canConfirm" @click="confirm" :class="'btn btn-' + color">
              {{ confirmTitle ?? title }}
            </button>
          </div>
        </slot>
      </template>
    </modal>
  </transition>
</template>

<script>
import ButtonWithModal from './ButtonWithModal'
import Modal from './Modal'

export default {
  emits: ['confirm', 'shown', 'hidden', 'abort'],
  components: {
    Modal,
    ButtonWithModal
  },
  data () {
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
    confirmTitle: {
      type: String,
      default: null
    },
    canConfirm: {
      type: Boolean,
      default: true
    },
    canAbort: {
      type: Boolean,
      default: false
    },
    abortTitle: {
      type: String,
      required: false
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
    abort: function () {
      this.$emit('abort')
      this.show = false
    },
    confirm: function () {
      this.$emit('confirm')
      this.show = false
    }
  }
}
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

</style>
