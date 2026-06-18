<template>
  <button-with-modal-confirm
      :button-disabled="patching" :title="$t('_action.edit_map.title')"
      :confirm-title="$t('_action.save_changes')" :can-confirm="canConfirm"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </template>

    <map-form :template="map" :maps="maps" @update="patch = $event" />
    <file-form @update="file = $event" :pdf-mode="true" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../domain/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapForm from '../Form/MapForm'
import FileForm from '../Form/FileForm'

export default {
  components: {
    FileForm,
    MapForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
      file: null,
      patch: null,
      patching: false
    }
  },
  props: {
    map: {
      type: Object,
      required: true
    },
    maps: {
      type: Array,
      required: true
    },
  },
  computed: {
    canConfirm: function () {
      return !!(this.pendingPatch || this.file)
    },
    pendingPatch: function () {
      return this.patch && Object.keys(this.patch).length
    }
  },
  methods: {
    confirm: async function () {
      this.patching = true

      if (this.file) {
        await api.postMapFile(this.map, this.file, this.$t('_action.edit_map.replaced_map_file'))
        this.file = null
      }

      if (this.pendingPatch) {
        await api.patch(this.map, this.patch, this.$t('_action.edit_map.saved'))
        this.patch = null
      }

      this.patching = false
    }
  }
}
</script>
