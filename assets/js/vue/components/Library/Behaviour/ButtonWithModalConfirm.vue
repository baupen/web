<template>
  <button @click="show = !show" :disabled="buttonDisabled" :class="buttonClasses">
    <slot name="button-content">
      {{ title }}
    </slot>
  </button>
  <transition name="fade">
    <modal v-if="show" :size="modalSize" @hide="show = false" :title="title" @keydown.esc.stop="show = false">
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
            <custom-checkbox for-id="repeat-confirm" v-if="repeatConfirmLabel" :label="repeatConfirmLabel">
              <input
                  class="form-check-input" type="checkbox" id="repeat-confirm"
                  v-model="repeatConfirm"
                  :true-value="true"
                  :false-value="false">
            </custom-checkbox>
            <button type="submit" :disabled="!canConfirm || buttonDisabled" @click="confirm" :class="'btn btn-' + color">
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
import CustomCheckbox from "../FormInput/CustomCheckbox.vue";

export default {
  emits: ['confirm', 'shown', 'hidden', 'abort'],
  components: {
    CustomCheckbox,
    Modal,
    ButtonWithModal
  },
  data() {
    return {
      show: false,
      repeatConfirm: false,
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
    buttonSize: {
      type: String,
      default: null
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
    },
    active: {
      type: Boolean,
      default: false
    },
    repeatConfirmLabel: {
      type: String,
      default: null
    }
  },
  computed: {
    buttonClasses: function () {
      let classes = 'btn'
      classes += (this.active ? ' btn-': ' btn-outline-') + this.color
      if (this.buttonSize) {
        classes += ' btn-' + this.buttonSize
      }

      return classes
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
      this.show = this.repeatConfirm
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
