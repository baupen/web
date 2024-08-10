<template>
  <custom-checkbox-field
      @click="startSelectPosition" :label="label">
    <input class="form-check-input" type="checkbox"
           :checked="position">
  </custom-checkbox-field>
  <transition name="fade">
    <modal v-if="show" size="lg" @hide="show = false" :title="$t('_action.select_map_position.title')" @keydown.esc.stop="show = false">
      <template v-slot:body>
        <img ref="map" :src="src" class="img-fluid img-within-modal img-set-position border" :alt="'image of ' + map.name" @click="selectPosition" />
      </template>
      <template v-slot:after-body>
        <slot name="footer">
          <div class="modal-footer">
            <button type="submit" @click="selectNullPosition" class="btn btn-light me-auto">
              {{ $t('_action.select_map_position.no_position') }}
            </button>
            <div class="alert alert-info p-1 ps-2 pe-2 m-0">{{ $t('_action.select_map_position.help') }}</div>
          </div>
        </slot>
      </template>
    </modal>
  </transition>
</template>

<script>
import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import Modal from '../Library/Behaviour/Modal'
import CustomCheckboxField from "../Library/FormLayout/CustomCheckboxField.vue";
import {api} from "../../services/api";
import TooltipText from "../Library/View/TooltipText.vue";

export default {
  emits: ['selected'],
  components: {
    TooltipText,
    CustomCheckboxField,
    Modal,
    ButtonWithModal
  },
  data() {
    return {
      show: false,
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
    position: {
      type: Object,
      required: false,
    },
  },
  computed: {
    src: function () {
      if (!this.map.fileUrl) {
        return null
      }

      return api.getIssuesRenderLink(this.constructionSite, this.map, {number: -1})
    },
    label: function () {
      let base = this.$t('_action.select_map_position.title');
      if (this.position?.isNull) {
        return `${base} (${this.$t('_action.select_map_position.no_position_set')})`
      }

      return base
    }
  },
  methods: {
    startSelectPosition: function () {
      if (this.position) {
        this.$emit('selected', null)
      } else {
        this.show = true
      }
    },
    selectPosition: function (event) {
      const rect = this.$refs['map'].getBoundingClientRect();

      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;

      const width = rect.width;
      const height = rect.height;

      const xPercentage = x / width;
      const yPercentage = y / height;

      this.$emit('selected', { isNull: false, x: xPercentage, y: yPercentage, zoomScale: 1})
      this.show = false
    },
    selectNullPosition: function () {
      this.$emit('selected', { isNull: true})
      this.show = false
    },
    abort: function () {
      this.$emit('selected', null)
      this.show = false
    },
  }
}
</script>


<style scoped>
.img-within-modal {
  max-height: 76vh;
  display: block;
  margin: 0 auto;
}

.img-set-position {
  cursor: crosshair;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

</style>
